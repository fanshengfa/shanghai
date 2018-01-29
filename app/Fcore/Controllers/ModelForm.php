<?php

namespace App\Fcore\Controllers;

trait ModelForm
{
    public function show($id)
    {
        return $this->edit($id);
    }

    public function update($id)
    {
        return $this->form()->update($id);
    }

    public function destroy($id)
    {
        if ($this->form()->destroy($id)) {
            return response()->json([
                'status'  => 'success',
                'status_code' => '200',
                'message' => trans('admin::lang.delete_succeeded'),
                'object' => null
            ]);
        } else {
            return response()->json([
                'status'  => 'error',
                'error' => [
                    'status_code' => strval("601"),
                    'message' => trans('admin::lang.delete_failed'),
                ]
            ]);
        }
    }

    public function store()
    {
        return $this->form()->store();
    }
}
