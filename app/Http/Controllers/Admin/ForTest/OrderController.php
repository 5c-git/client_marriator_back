<?php

namespace App\Http\Controllers\Admin\ForTest;

use App\Http\Controllers\Controller;
use App\Models\Order\Order;
use App\Models\Order\Task;
use Illuminate\Http\Request;

class OrderController extends Controller
{

    private string $view = 'orderTest';
    private string $objClass;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->objClass = Order::class;
    }

    public function list(Request $request)
    {
        $list = $this->objClass::get();
        return view('admin.forTest.'.$this->view.'.list', compact('list'));
    }

    public function delete(Request $request)
    {
        if ($request->id) {
            $orders = Order::where('id', $request->id)->get();
            foreach ($orders as $order) {

                $order->orderActivities()->delete();

                $order->acceptingUsers()->detach();

                $order->tasks->each(function ($task) {
                    $task->acceptingUsers()->detach();
                    $task->taskActivities()->delete();
                    $task->bid()->delete();
                    $task->delete();
                });

                $order->bids->each(function ($bid) {
                    $bid->acceptingUsers()->detach();
                    $bid->delete();
                });

                $order->delete();
            }
        }
        return redirect()->route($this->view . 'List');
    }

}
