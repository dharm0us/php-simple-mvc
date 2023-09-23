<?php

namespace SimpleMVC;

class BaseController
{

	protected $GET_Map;
	protected $POST_Map;

	public function run()
	{
		$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : null;
		$this->setMaps();
		$method = $_SERVER['REQUEST_METHOD'];
		if ($method == 'GET') {
			$this->runGet($action);
		} else if ($method == 'POST') {
			$this->runPost($action);
		}
	}

	protected function runGet($action)
	{
		$this->runMethod($action, $this->GET_Map);
	}

	protected function runPost($action)
	{
		$this->runMethod($action, $this->POST_Map);
	}

	protected function runMethod($action, $map)
	{
		if ($action && $map && isset($map[$action])) {
			$func = $map[$action];

			$func();
		} else {
			$this->defaultAction();
		}
	}

	protected function setMaps()
	{
		$this->GET_Map = array();
		$this->POST_Map = array();
	}

	protected function defaultAction()
	{
		$action = $_REQUEST['action'];
		$msg = "No handler found for action = $action";
		Log::error($msg);
		HttpUtils::badRequest();
	}
}
