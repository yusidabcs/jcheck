<?php namespace Bcscoder\Jcheckout;
use Illuminate\Session\SessionServiceProvider;

class JcheckoutBaseController extends \Controller {
	public $layout = 'jcheckout::template';
	public $setting;
	public $akunId;
	protected function setupLayout()
	{		
		if(\Session::has('akunid'))
		{
			$this->akunId =\Session::get('akunid');
			$this->setting = \Pengaturan::remember(1)->where('akunId','=',$this->akunId)->first();
			if ( ! is_null($this->layout))
			{
	            $ga = $this->setting->gAnalytics;

				$this->layout = \View::make($this->layout)
					->with('analytic',$ga)
	                ->with('kontak', $this->setting);

		        $this->layout->seo = \View::make('jcheckout::seostuff')
		            ->with('title',"Checkout - Rincian Belanja - ".$this->setting->nama)
		            ->with('description',$this->setting->deskripsi)
		            ->with('keywords',$this->setting->keyword);     
			}
		}
		else
		{
			$this->layout = \View::make($this->layout);
		}
	}
}