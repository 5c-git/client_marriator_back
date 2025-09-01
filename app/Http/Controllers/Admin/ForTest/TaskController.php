<?php

namespace App\Http\Controllers\Admin\ForTest;

use App\Http\Controllers\Controller;
use App\Models\Order\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{

    private string $view = 'taskTest';
    private string $objClass;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->objClass = Task::class;
    }

    public function list(Request $request)
    {
        $list = $this->objClass::get();
        return view('admin.forTest.'.$this->view.'.list', compact('list'));
    }

    public function delete(Request $request)
    {
        if ($request->id) {
            $tasks = Task::where('id', $request->id)->get();
            foreach ($tasks as $task) {

                $task->acceptingUsers()->detach();
                $task->taskActivities()->delete();

                $task->bid->each(function ($bid) {
                    $bid->acceptingUsers()->detach();
                    $bid->delete();
                });

                $task->delete();
            }
        }
        return redirect()->route($this->view.'List');
    }

}
