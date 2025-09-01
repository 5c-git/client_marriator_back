<?php

namespace App\Http\Controllers\Admin\ForTest;

use App\Http\Controllers\Controller;
use App\Models\Order\Bid;
use Illuminate\Http\Request;

class BidController extends Controller
{

    private string $view = 'bidTest';
    private string $objClass;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->objClass = Bid::class;
    }

    public function list(Request $request)
    {
        $list = $this->objClass::get();
        return view('admin.forTest.'.$this->view.'.list', compact('list'));
    }

    public function delete(Request $request)
    {
        if ($request->id) {
            $bids = Bid::where('id', $request->id)->get();
            foreach ($bids as $bid) {
                $bid->acceptingUsers()->detach();
                $bid->delete();
            }
        }
        return redirect()->route($this->view.'List');
    }

}
