<?php namespace Bcscoder\Jcheckout;

class JcheckoutBaseController extends \Controller {
	public $layout = 'jcheckout::template';
	public $setting;
	public $akunId;
	protected function setupLayout()
	{		
		$this->akunId =\Session::get('akunid');
		$this->setting = \Pengaturan::where('akunId','=',$this->akunId)->first();
		if ( ! is_null($this->layout))
		{
            $ga = $this->setting->gAnalytics;

			$this->layout = \View::make($this->layout)
				->with('analytic',$ga)
                ->with('kontak', $this->setting);
		}
	}
}