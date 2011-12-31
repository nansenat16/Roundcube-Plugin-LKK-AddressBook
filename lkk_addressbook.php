<?php

class lkk_addressbook extends rcube_plugin{
	public $task = 'mail';
	public $action = 'compose';
	private $addrs = array();

	function init(){
		$rcmail = rcmail::get_instance();
		if ($rcmail->action == 'compose'){
			$this->include_script('lkk_abk.js');
		}

		$this->add_texts('localization/');
		$this->register_action('plugin.lkk_abk_show',array($this,'show'));
		$this->register_handler('plugin.target',array($this,'get_t'));
		$this->register_handler('plugin.act_title',array($this,'set_t'));
		$this->register_handler('plugin.table',array($this,'tab'));
	}

	function show(){
		if (get_input_value('t', RCUBE_INPUT_GET) !== null) {
			$addr=rcmail::get_instance()->get_address_book('sql');
			$addr->set_pagesize(9999);
			$rt=$addr->list_records(array('name','email'));
			while($u=$rt->next()){
				$this->addrs[]=array('name'=>$u['name'],'email'=>$u['email']);
			}
			$output=rcmail::get_instance()->output;
			$output->set_pagetitle($this->gettext('title'));
			$output->send('lkk_addressbook.mini_book');
		}
	}

	function get_t(){
		return get_input_value('t', RCUBE_INPUT_GET);
	}

	function set_t(){
		return $this->gettext('add_to').$this->gettext($this->get_t());
	}

	function tab(){
		$addr=$this->addrs;
		$html='<table id="addrs"><tr><th>'.$this->gettext('name').'</th><th>'.$this->gettext('mail').'</th></tr>'."\n";
		for($n=0;$n<count($addr);$n++){
			$html.='<tr><td>'.$addr[$n]['name'].'</td><td>';
			for($m=0;$m<count($addr[$n]['email']);$m++){
				$html.='<span class="mail_addr">'.$addr[$n]['email'][$m].'</span>';
			}
			$html.="</td></tr>\n";
		}
		$html.='</table>';
		return $html;
	}
}

?>
