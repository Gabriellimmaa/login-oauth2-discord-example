<?php

	session_start();
	include('Login.php');

	if (!defined('INCLUDE_PATH')) define('INCLUDE_PATH', 'http://localhost/exampleOauthDiscord/');
	if (!defined('INCLUDE_PATH_PAINEL')) define('INCLUDE_PATH_PAINEL', INCLUDE_PATH . 'painel/');
	if (!defined('CAMINHO_PAGINA_PAINEL')) define('CAMINHO_PAGINA_PAINEL', '/exampleOauthDiscord/');

	// Base dos documentos
	if (!defined('BASE_DIR')) define('BASE_DIR', __DIR__ . '/');

	// Dados para oauth2 do discord
	// Definindo o ID da aplicacao (pega no site do discord na aba de Oauth2)
	define('OAUTH2_CLIENT_ID', 'Coloque_aqui_seu_ID');
	// Definindo a TOKEN da aplicacao (pega no site do discord na aba de Oauth2)
	define('OAUTH2_CLIENT_SECRET', 'Coloque_aqui_sua_token');
	$authorizeURL = 'https://discord.com/api/oauth2/authorize';
	$tokenURL = 'https://discord.com/api/oauth2/token';
	$revokeURL = 'https://discord.com/api/oauth2/token/revoke';
	// Esse redirect tem q ser o mesmo que colocou no site do discord
	$redirect = "http://localhost/brasilRolePlayXbox/";
	// Esse link é utilizado para pegar as informacoes do usuario 
	$apiURLBase = 'https://discord.com/api/users/@me';
	// Esse link é utilizado para pegar as informacoes de um usuario relacionados a um servidor especifico no caso meu
	// o servidor é esse id: 579126159070068746 ... ele pega os cargos do user nesse servidor
	$apiURLGuild = 'https://discord.com/api/users/@me/guilds/579126159070068746/member';

	// Funções utilizadas para o fazer request
	function apiRequest($url, $post = FALSE, $headers = array())
	{
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

		$response = curl_exec($ch);

		if ($post)
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));

		$headers[] = 'Accept: application/json';

		if (session('access_token'))
			$headers[] = 'Authorization: Bearer ' . session('access_token');

		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

		$response = curl_exec($ch);
		return json_decode($response);
	}

	function get($key, $default = NULL)
	{
		return array_key_exists($key, $_GET) ? $_GET[$key] : $default;
	}

	function session($key, $default = NULL)
	{
		return array_key_exists($key, $_SESSION) ? $_SESSION[$key] : $default;
	}

	// Função para logout
	function logout($url, $data = array())
	{
		$ch = curl_init($url);
		curl_setopt_array($ch, array(
			CURLOPT_POST => TRUE,
			CURLOPT_RETURNTRANSFER => TRUE,
			CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
			CURLOPT_HTTPHEADER => array('Content-Type: application/x-www-form-urlencoded'),
			CURLOPT_POSTFIELDS => http_build_query($data),
		));
		$response = curl_exec($ch);
		return json_decode($response);
	}
