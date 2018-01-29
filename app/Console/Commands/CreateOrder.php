<?php

namespace App\Console\Commands;


use App\Model\OrderGoods;
use App\Model\OrderPlace;
use App\Model\RouteAuto;
use App\Model\RouteDriver;
use App\Model\RouteGoods;
use App\Model\RoutePlace;
use App\Model\RoutePlan;
use App\Model\Card;
use App\Model\Driver;
use App\Model\Overdraft;
use App\Model\Company;
use App\Model\Auto;
use App\Model\Place;


use DB;
use App\Model\Route;
use App\Model\Order;
use Encore\Admin\Form;
use Encore\Admin\Facades\Admin;
use Illuminate\Console\Command;

class CreateOrder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'createorder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '创建运单';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->createOrder();
    }

    public function createOrder()
    {
        $routes = Route::where(['status' => 1])->get();
        foreach ($routes as $route) {
            $route_id = $route->id;
            $routePlan = RoutePlan::where(['route_id' => $route_id])->where(function ($query) {
                $query->whereRaw('(curdate()-time_base)%time_type=0 and curtime()>create_time');
                $query->whereRaw('now() < concat(date_add(curdate(), interval guess_start_time_type day)," ",guess_start_time)');
            })->selectRaw('
                *, curdate() curdate, curtime() curtime, 
                curdate() guess_start_date,
                date_add(curdate(), interval `guess_end_time_type` day) guess_end_date')->orderBy('create_time', 'asc')->get();
            $routeDriver = RouteDriver::where(['route_id'=>$route_id])->first();
            $routeAuto = RouteAuto::where(['route_id'=>$route_id])->first();
            foreach ($routePlan as $plan) {
                $guess_start_time = $plan->guess_start_date . ' ' . $plan->guess_start_time;
                $guess_end_time = $plan->guess_end_date . ' ' . $plan->guess_end_time;
                $order = Order::where(['route_id' => $route_id, 'guess_start_time' => $guess_start_time, 'guess_end_time' => $guess_end_time])->first();
                if (!empty($order)) {
                    echo "==order==exists==order_id{$order->id}==route_id{$route_id}\n";
                    continue;
                }
                $order = [
                    'route_id' => $route['id'],
                    'partner_id' => $route['partner_id'],
                    'dispatcher_id' => 0,
                    'company_id' => $route['company_id'],
                    'status' => 20,
                    'driver_id' => $routeDriver->driver_id,
                    'auto_id' => $routeAuto->auto_id,
                    'guess_start_time' => $guess_start_time,
                    'guess_end_time' => $guess_end_time,
                    'guess_kilometer' => $route->guess_kilometer,
                    'guess_fuel' => $route->guess_fuel,
                    'guess_weight' => $route->guess_weight,
                    'guess_cost' => $route->guess_cost,
                    'send_company' => $route->send_company,
                    'send_contact_name' => $route->send_contact_name,
                    'send_contact_mobile' => $route->send_contact_mobile,
                    'recevie_company' => $route->recevie_company,
                    'recevie_contact_name' => $route->recevie_contact_name,
                    'recevie_contact_mobile' => $route->recevie_contact_mobile
                ];
                try {
                    DB::beginTransaction();
                    $order = Order::create($order);
                    $routeGoods  = RouteGoods::where('route_id',$route_id)->get();
                    foreach ($routeGoods as $orderGoods) {
                        $orderGoods = $orderGoods->toArray();
                        unset($orderGoods['route_id']);
                        $orderGoods['order_id'] = $order->id;
                        OrderGoods::create($orderGoods);
                    }
                    $routePlace = RoutePlace::with('place')->where('route_id',$route_id)->orderBy('order', 'asc')->orderBy('id', 'asc')->get();
                    foreach($routePlace as $orderPlace) {
                        $orderPlace = $orderPlace->toArray();
                        $orderPlace['polygon'] = $orderPlace['place']['polygon'];
                        $orderPlace['order_id'] = $order->id;
                        $orderPlace['guess_start_time'] = date('Y-m-d H:i:s',strtotime(date('Y-m-d')." {$orderPlace['guess_start_time']} +{$orderPlace['guess_start_time_type']} day"));
                        $orderPlace['guess_end_time']   = date('Y-m-d H:i:s',strtotime(date('Y-m-d')." {$orderPlace['guess_end_time']} +{$orderPlace['guess_end_time_type']} day"));
                        $orderPlace['status'] = '0';
                        OrderPlace::create($orderPlace);
                    }
                    echo "==order==create==order_id{$order->id}==route_id{$route_id}\n";
                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                    echo "==error==".$e->getMessage()."\n";
                }
            }

        }
    }

}
