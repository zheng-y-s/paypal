<?php
namespace Paypal;
use Paypal/Test;

class Api
{
	public function index()
	{
		$helper = new Test;
		echo $helper->index();
	}
}