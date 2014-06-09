<?php namespace Bcscoder\Jcheckout;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;

class FinPay extends Model {
	public $timestamps = false;

	protected $table = '195_transaction';

	public function order()
	{
		return \Order::where('kodeOrder','=',$this->invoice)->first();
	}

	public function preOrder()
	{
		return \PreOrder::where('kodePreOrder','=',$this->invoice)->first();
	}

}