<?php

namespace crmeb\basic;

abstract class BaseController
{
	protected $request;
	protected $app;
	protected $batchValidate = false;
	protected $middleware = [];

	public function __construct(\think\facade\App $app)
	{
		$this->app = $app;
		$this->request = app('request');
		$this->initialize();
	}

	protected function initialize()
	{
	}

	final protected function validate(array $data, $validate, $message = NULL, bool $batch = false)
	{
		if (is_array($validate)) {
			$v = new \think\facade\Validate();
			$v->rule($validate);
		}
		else {
			if (strpos($validate, '.')) {
				list($validate, $scene) = explode('.', $validate);
			}

			$class = (false !== strpos($validate, '\\') ? $validate : $this->app->parseClass(__FUNCTION__, $validate));
			$v = new $class();

			if (!empty($scene)) {
				$v->scene($scene);
			}
			if (is_string($message) && empty($scene)) {
				$v->scene($message);
			}
		}

		if (is_array($message)) {
			$v->message($message);
		}
		if ($batch || $this->batchValidate) {
			$v->batch(true);
		}

		return $v->failException(true)->check($data);
	}

	final protected function success($msg = 'ok', ?array $data = NULL)
	{
		return app('json')->success($msg, $data);
	}

	final protected function fail($msg = __FUNCTION__, ?array $data = NULL)
	{
		return app('json')->fail($msg, $data);
	}

	final protected function make(int $status, string $msg, ?int $code = NULL, ?array $data = NULL)
	{
		$json = app('json');

		if ($code) {
			$json->code($code);
		}

		return $json->make($status, $msg, $data);
	}

	final protected function checkAuthDecrypt()
	{
		try {
			$res = $this->authorizationDecryptCrmeb(true);
			if ($res && is_array($res)) {
				list($domain, $recordCode, $installtime) = $res;
				$time = $installtime + 1296000;

				if ($time < time()) {
					list($domain, $recordCode, $installtime) = $res;
					$time = $installtime + 1296000;
					//return $this->success('您得授权已过期请及时前往CRMEB官方进行授权', ['auth' => false]);
				}
				else {
					list($domain, $recordCode, $installtime) = $res;
					$time = $installtime + 1296000;
					//$nowTime = ($time - time()) / 86400;
					//return $this->success('您得授权证书还有' . (int) $nowTime . '天过期,请及时前往CREMB官方进行授权认证!', ['auth' => false]);
				}
			}
			else if ($res === true) {
				return $this->fail();
			}
			else {
				list($domain, $recordCode, $installtime) = $res;
				$time = $installtime + 1296000;
				//return $this->fail('授权文件读取错误');
			}
		}
		catch (\RuntimeException $e) {
			list($domain, $recordCode, $installtime) = $res;
			$time = $installtime + 1296000;
			//return $this->fail('授权文件读取错误');
		}
	}

	final private function authorizationDecryptCrmeb(bool $bool = false)
	{
		$authorizationExtactPath = AUTHORIZATION_EXTACT;
		$authorizationExtacttext = AUTHORIZATION_TEXT;
		/* if (!$authorizationExtactPath || !is_file($authorizationExtactPath)) {
			throw new \RuntimeException('授权证书丢失', 42007);
		} */
		/* if (!$authorizationExtacttext || !is_file($authorizationExtacttext)) {
			throw new \RuntimeException('授权文件丢失', 42006);
		} */
		if ($authorizationExtactPath && $authorizationExtacttext) {
			$publicDecrypt = function(string $data, string $publicKey) {
				$decrypted = '';
				$pu_key = openssl_pkey_get_public(file_get_contents($publicKey));
				$plainData = str_split(base64_decode($data), 128);

				foreach ($plainData as $chunk) {
					$str = '';
					$decryptionOk = openssl_public_decrypt($chunk, $str, $pu_key);

					if ($decryptionOk === false) {
						return false;
					}

					$decrypted .= $str;
				}

				return $decrypted;
			};
			$encryptStr = file_get_contents($authorizationExtacttext);

			/* if (!$encryptStr) {
				throw new \RuntimeException('授权文件内容丢失', 42005);
			}
 */
			$resArray = explode('==', $encryptStr);

			/* if (!is_array($resArray)) {
				throw new \RuntimeException('授权文件有变动无法解析', 42008);
			}
			else {
				list($encryptStr, $recordCode) = explode(',', $encryptStr);
			}

			if (!isset($recordCode)) {
				$recordCode = '';
			} */

			$data = $publicDecrypt($encryptStr, $authorizationExtactPath);

			if ($data) {
				$data = json_decode($data);
				$installtime = @filectime(app()->getRootPath() . 'public' . DS . 'install' . DS . 'install.lock');
				if (isset($data->domain) && isset($data->expire) && isset($data->version)) {
					$res = time() <= $installtime + $data->expire;

					if ($res) {
						if ($bool && ($data->domain === '*')) {
							return [$data->domain, $recordCode, $installtime];
						}
						if (($data->domain === '*') || in_array(request()->host(), ['127.0.0.1', 'localhost'])) {
							return true;
						}
						else if ($data->domain === request()->host()) {
							return true;
						}
						else {
							return true;
							//throw new \RuntimeException('您的授权域名和访问域名不一致!', 42000);
						}
					}
					/* else {
						throw new \RuntimeException('您的授权已到期', 42001);
					} */
				}
			}
			/* else {
				throw new \RuntimeException('授权文件有变动无法解析', 42003);
			} */
		}

		//throw new \RuntimeException('授权失败', 42004);
	}

	final protected function makePostForm(string $title, array $field, $url, string $method = 'POST')
	{
		try {
			$this->authorizationDecryptCrmeb();
			$form = \FormBuilder\Form::create((string) $url);
			$form->setMethod($method);
			$form->components($field);
			$form->setTitle($title);
			$rules = $form->getRules();
			$title = $form->getTitle();
			$action = $form->getAction();
			$method = $form->getMethod();
			$info = '';
			$status = true;
			return $this->success(compact('rules', 'title', 'action', 'method', 'info', 'status'));
		}
		catch (\Throwable $e) {
			$rules = [];
			$title = $e->getMessage();
			$info = '请联系CRMEB官方进行授权认证';
			$status = false;
			$action = '';
			$method = 'get';
			return $this->success(compact('rules', 'title', 'action', 'method', 'info', 'status'));
		}
	}

	final protected function attr_format($arr)
	{
		$data = [];
		$res = [];
		$count = count($arr);

		if (1 < $count) {
			for ($i = 0; $i < ($count - 1); $i++) {
				if ($i == 0) {
					$data = $arr[$i]['detail'];
				}

				$rep1 = [];

				foreach ($data as $v) {
					foreach ($arr[$i + 1]['detail'] as $g) {
						$rep2 = ($i != 0 ? '' : $arr[$i]['value'] . '_$_') . $v . '-$-' . $arr[$i + 1]['value'] . '_$_' . $g;
						$tmp[] = $rep2;

						if ($i == $count - 2) {
							foreach (explode('-$-', $rep2) as $k => $h) {
								$rep3 = explode('_$_', $h);
								$rep4['detail'][$rep3[0]] = (isset($rep3[1]) ? $rep3[1] : '');
							}

							if ($count == count($rep4['detail'])) {
								$res[] = $rep4;
							}
						}
					}
				}

				$data = (isset($tmp) ? $tmp : []);
			}
		}
		else {
			$dataArr = [];

			foreach ($arr as $k => $v) {
				foreach ($v['detail'] as $kk => $vv) {
					$dataArr[$kk] = $v['value'] . '_' . $vv;
					$res[$kk]['detail'][$v['value']] = $vv;
				}
			}

			$data[] = implode('-', $dataArr);
		}

		return [$data, $res];
	}

	final protected function getAuth()
	{
		try {
			$auth = $this->authorizationDecryptCrmeb();
		}
		catch (\RuntimeException $e) {
			$auth = false;
		}

		$defaultRecordCode = '00000000';
		$recordCode = '';
		$encryptStr = file_get_contents(AUTHORIZATION_TEXT);

		/* if (!$encryptStr) {
			throw new \RuntimeException('授权文件内容丢失', 42005);
		} */

		$resArray = explode('==', $encryptStr);

		if (!is_array($resArray)) {
			list($encryptStr, $recordCode) = explode(',', $encryptStr);
			//throw new \RuntimeException('授权文件有变动无法解析', 42008);
		}
		else {
			list($encryptStr, $recordCode) = explode(',', $encryptStr);
		}

		$recordCode = $recordCode ?? false;
		if (!$auth || ($defaultRecordCode == $recordCode)) {
			$res = \crmeb\services\HttpService::postRequest('http://shop.crmeb.net/auth/business/auth', ['domain' => $this->request->host(), 'version' => get_crmeb_version()]);

			if ($res !== false) {
				$res = json_decode($res, true);
				if (isset($res['code']) && ($res['code'] == 200) && isset($res['data']) && $res['data']) {
					$dataContent = $res['data'];
					$res = file_put_contents(app()->getRootPath() . 'ZEuduEXx9em36aYgTGvhQIq.txt', $dataContent['auto_content'] . ',' . $dataContent['auth_code']);
					return $this->success(['auth_code' => $dataContent['auth_code'], 'auth' => true]);
				}
			}

			return $this->fail();
		}
		else {
			if ($recordCode) {
				if ($recordCode == $defaultRecordCode) {
					return $this->fail();
				}

				return $this->success(['auth_code' => $recordCode, 'auth' => true]);
			}

			return $this->fail();
		}
	}
}

?>