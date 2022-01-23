<?php
ob_start();
error_reporting(0);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('max_execution_time', 300);

// Incluir o config é onde esta nossas variaves "globais"
include('config.php');

// Primeiro passo é enviar o usuario para a pagina de autenticação do discord
if (get('action') == 'login') {

	// Aqui seria um json com as informações necessaria que a documentacao do discord pede
	$params = array(
		// ID da sua aplicacao discord 
		'client_id' => OAUTH2_CLIENT_ID,
		// A página que irá após completar a autencicacao (tem que adicionar o link no redirect dentro do site do discord)
		'redirect_uri' => $redirect,
		// Por padrao utiliza code pois estamos enviando
		'response_type' => 'code',
		// Aqui você coloca oque quer puxar do usuario, existe mais opcoes na documentacao
		'scope' => 'identify guilds guilds.members.read email'
	);

	// Enviando o usuario para a pagina de autenticacao
	header('Location: https://discord.com/api/oauth2/authorize' . '?' . http_build_query($params));
	die();
}

// Esse codigo se encaixa para quando o discord retorna o usuario
if (get('code')) {
	// Extraindo a autenticacao com a token
	$token = apiRequest($tokenURL, array(
		"grant_type" => "authorization_code",
		'client_id' => OAUTH2_CLIENT_ID,
		// Token secreta da sua aplicacao, pega no site do discord na aba de Oauth2
		'client_secret' => OAUTH2_CLIENT_SECRET,
		'redirect_uri' => $redirect,
		'code' => get('code')
	));
	$logout_token = $token->access_token;
	$_SESSION['access_token'] = $token->access_token;

	//Volta para o site normalmente
	header('Location: ' . $_SERVER['PHP_SELF']);
}

// Extraindo os dados do usuario e passando para session
if (session('access_token')) {
	$user = apiRequest($apiURLBase);
	$guild = apiRequest($apiURLGuild);

	// Exibe todas as informacoes possiveis extraidas do $user (name, id, avatar, email...)
	// echo '<h4>Usuario: ' . $user->username . '</h4>';
	// echo '<pre>';
	// print_r($guild);
	// echo '</pre>';

	// Exibe todas as informacoes possiveis extraidas do $guild (servidor especifico configurado e seus cargos dentro do servidor)
	// echo '<h4>Usuario: ' . $user->username . '</h4>';
	// echo '<pre>';
	// print_r($guild);
	// echo '</pre>';

	// Armazenando os valores
	$_SESSION['id'] = $user->id;
	$_SESSION['login'] = true;
	$_SESSION['user'] = $user->username;
	$_SESSION['email'] = $user->email;
	// Transformando o avatar dele em uma url para utilizar no restante do site
	$_SESSION['img'] = "https://cdn.discordapp.com/avatars/" . $_SESSION['id'] . "/" . $user->avatar . ".png";
	$_SESSION['redirect'] = 'home.php';
}
?>

<!DOCTYPE html>
<html>

<head>
	<meta charset="UTF-8">
	<link rel="icon" href="favicon.ico" type="image/ico">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link href="https://fonts.googleapis.com/css?family=Lato:400,700&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Sofia">
	<link href="https://fonts.googleapis.com/css2?family=Fugaz+One&display=swap" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&display=swap" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Open+Sans:wght@300&display=swap" rel="stylesheet">
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link rel="stylesheet" href="<?php echo INCLUDE_PATH ?>fontawesome/all.min.css">
	<script src="https://kit.fontawesome.com/c2eaecad4c.js"></script>
	<link rel="stylesheet" href="<?php echo INCLUDE_PATH ?>resources/bootstrap/bootstrap.min.css">
	<link rel="stylesheet" href="<?php echo INCLUDE_PATH ?>resources/css/style.css">
	<link rel="stylesheet" href="<?php INCLUDE_PATH ?>resources/css/index.css">
	<script>
		if (window.history.replaceState) {
			window.history.replaceState(null, null, window.location.href);
		}
	</script>
	<title>Brasil Roleplay® [XBOX]🇧🇷</title>
</head>

<style>
	.container-principal {
		padding-top: <?php
						$url = isset($_GET['url']) ? $_GET['url'] : 'home';
						if ($url !== "home") {
							echo "50px";
						}
						?>;
		z-index: -1;
	}
</style>

<body>
	<base base="<?php echo INCLUDE_PATH; ?>" />

	<header>
		<?php
		if (Login::logado() == false) {
			$varLogin = "Login";
		} else {
			$varLogin = "Deslogar";
			$userImg = $_SESSION['img'];
		}
		if (isset($_POST['acao'])) {
			if (Login::logado() == true) {
				if (isset($_SESSION['login'])) {
					Login::loggout();
				}
			} else {
				header('Location:' .  INCLUDE_PATH . "signin");
			}
		}
		?>
		<div class="center">
			<nav class="desktop left">
				<ul>
					<li><a href="<?php echo INCLUDE_PATH; ?>">Início</a></li>
					<li><a href="<?php echo INCLUDE_PATH; ?>eventos">Eventos</a></li>
					<li><a href="<?php echo INCLUDE_PATH; ?>rank">Rank</a></li>
					<li><a href="<?php echo INCLUDE_PATH; ?>detran">Detran</a></li>
					<li><a href="<?php echo INCLUDE_PATH; ?>membro">Membro</a></li>
					<li><a href="<?php echo INCLUDE_PATH; ?>loja">Loja</a></li>
					<li><a href="<?php echo INCLUDE_PATH; ?>carros">Carros</a></li>
				</ul>
			</nav>
			<nav class="desktop right">
				<form method="post">
					<ul>
						<li><a target="_blank" href="https://www.instagram.com/Brasil_roleplay_brrp/"><i class="fab fa-instagram"></i></a></li>
						<li><a target="_blank" href="https://discord.gg/brrp"><i class="fab fa-discord"></i></a></li>
						<?php
						if (Login::logado() == true) {
							echo '<li style="border-left: solid 2px white"><a href="' . INCLUDE_PATH . 'user?id=' . $_SESSION['id'] . '">Meu perfil</a></li>';
						}
						?>
						<?php
						if (Login::logado() == true) {
							echo '<li style="border-left: solid 2px white"><input type="submit" name="acao" value=' . $varLogin . '><i style="margin-left: 10px;vertical-align: middle;color: white" class="fas fa-sign-in-alt"></i></li>';
							echo '<img class="navimg" src=' . $userImg . '>';
						} else {
							echo '<li><a href="?action=login" class="login">Entrar</a></li>';
						}
						?>
					</ul>
				</form>
			</nav>
			<div class="mobile-wrapper">
				<nav class="mobile-left" style="width: 30%;">
					<a style="font-size: 20px;margin-left: 10px;position: relative;text-decoration: none; color:#ffffff; z-index: 999;" target="_blank" href="https://www.instagram.com/Brasil_roleplay_brrp/"><i class="fab fa-instagram"></i></a>
					<a style="font-size: 20px;margin-left: 10px;position: relative;text-decoration: none; color:#ffffff; z-index: 999;" target="_blank" href="https://discord.gg/brrp"><i class="fab fa-discord"></i></a>
				</nav>
				<nav class="mobile right" style="width: 70%;">
					<div class="conteudo-mobile">
						<div class="menu-mobile-wraper" style="width: 50%;">
							<?php
							if (Login::logado() == true) {
								echo '<a href="' . INCLUDE_PATH . 'user?id=' . $_SESSION['id'] . '">Meu perfil</a>';
							}
							?>
							<?php
							if (Login::logado() == true) {
								echo '<input type="submit" name="acao" value=<i style="margin-left: 10px;vertical-align: middle;color: white" class="fas fa-sign-in-alt"></i>';
							} else {
								echo '<input type="submit" name="acao" value=' . $varLogin . '>';
							}
							?>
						</div>
						<div class="img-mobile" style="width: 50%;">
							<?php
							if (Login::logado() == true) {
								echo '<img class="navimg" src=' . $userImg . '>';
							}
							?>
							<h3 style="text-align: right;"><i class="fa fa-bars"></i></h3>
						</div>
					</div>
					<ul>
						<p class="line"></p>
						<li><a href="<?php echo INCLUDE_PATH; ?>">Início</a></li>
						<p class="line"></p>
						<li><a href="<?php echo INCLUDE_PATH; ?>eventos">Eventos</a></li>
						<p class="line"></p>
						<li><a href="<?php echo INCLUDE_PATH; ?>rank">Rank</a></li>
						<p class="line"></p>
						<li><a href="<?php echo INCLUDE_PATH; ?>detran">Detran</a></li>
						<p class="line"></p>
						<li><a href="<?php echo INCLUDE_PATH; ?>membro">Membro</a></li>
						<p class="line"></p>
						<li><a href="<?php echo INCLUDE_PATH; ?>loja">Loja</a></li>
						<p class="line"></p>
						<li><a href="<?php echo INCLUDE_PATH; ?>carros">Carros</a></li>
						<p class="line"></p>
					</ul>
				</nav>
			</div>
			<div class="clear"></div>
		</div>
	</header>

	<div class="container-principal">
		<video autoplay="true" muted loop>
			<source src="https://cdn.discordapp.com/attachments/771470980324524043/933171823401635920/video.mp4" type="video/mp4">
		</video>
		<div class="border-green" style="border-bottom: none;">
			<div class="container align-items-center flex-column d-flex justify-content-center text-center">
				<div class="title">
					<h1>Como jogar</h1>
				</div>
				<p><label class="bold">1°-</label> vá até o chat <a target="_blank" href="https://discord.gg/brrp">#fazer-whitelist</a> e clique na reação, logo em seguida será criado um novo chat parecido com este <label class="discord">#📝whitelist-0157-537075557225988126</label>. Depois disso basta você abrir o chat que será criado e seguir as instruções do bot. </p>
				<p><label class="bold">2°-</label> Após ter realizado a sua whitelist, aguarde ser aprovado ou reprovado, com a sua aprovação na whitelist você terá que ir ao chat <label class="discord">#comando-brrp</label> neste chat haverá um link que corresponde ao comando do servidor, necessário para entrar na sessão do RP. Vale ressaltar que para entrar na sessão do RP tem que deixar o comando como principal, e para não ser expulso do comando é obrigatório vincular sua conta do XBOX com a do GTA.</p>
				<p><label class="bold">3°-</label> Como entrar na sessão do RP? entre no chat <label class="discord">#iniciando-rp</label> e envie uma mensagem para o nick inserido lá (Gamertag na Xbox live) juntamente com a senha inserida.</p>
				<p><label class="bold">4°-</label> Caso tenha ficado quaisquer outro tipo de dúvida basta procurar a Equipe Brasil Roleplay Staff, que estamos prontos para te ajudar !!!!</p>
			</div>
		</div>

		<div class="container align-items-center flex-column d-flex justify-content-center text-center">
			<div class="container title">
				<h1>Regras</h1>
			</div>

			<div class="row m-0">
				<div class="col-lg-3 col-md-6 ">
					<a style="text-decoration: none;" href="<?php echo INCLUDE_PATH; ?>regrasGerais">
						<div class="card  text-center xs-mb-30 sm-mb-30 light-border" data-wow-delay="0.2s">
							<i class="card-icon fas fa-book fa-2x"></i>
							<h4 class="mt-0 font-600">Regras Gerais</h4>
						</div>
					</a>
				</div>
				<div class="col-lg-3 col-md-6 ">
					<a style="text-decoration: none;" href="<?php echo INCLUDE_PATH; ?>regrasDiscord">
						<div class="card text-center xs-mb-30 sm-mb-30 light-border" data-wow-delay="0.2s">
							<i class="card-icon fab fa-discord fa-2x"></i>
							<h4 class="mt-0 font-600">Regras Discord</h4>
						</div>
					</a>
				</div>
				<div class="col-lg-3 col-md-6 ">
					<a style="text-decoration: none;" href="<?php echo INCLUDE_PATH; ?>regrasAcao">
						<div class="card text-center xs-mb-30 sm-mb-30 light-border" data-wow-delay="0.2s">
							<i class="card-icon fas fa-archway fa-2x"></i>
							<h4 class="mt-0 font-600">Regras Ação</h4>
						</div>
					</a>
				</div>
				<div class="col-lg-3 col-md-6 ">
					<a style="text-decoration: none;" href="<?php echo INCLUDE_PATH; ?>regrasFaccao">
						<div class="card text-center xs-mb-30 sm-mb-30 light-border" data-wow-delay="0.2s">
							<i class="card-icon fa fa-user-ninja fa-2x"></i>
							<h4 class="mt-0 font-600">Regras Facção</h4>
						</div>
					</a>
				</div>
			</div>
		</div>

		<div class="border-green" style="border-bottom: none;">
			<div class="container align-items-center flex-column d-flex justify-content-center text-center">
				<div class="container title">
					<h1>Comandos Bot</h1>
				</div>
				<div class="divtabela" style="padding-top: 0px">
					<div class="table-responsive table">
						<table style="margin-bottom: 0px;" class="table table-comandos">
							<thead>
								<tr>
									<th style="background-color: var(--primary-color);color: white;border-radius: 5px 0px 0px 0px">Comando</th>
									<th style="background-color: var(--primary-color);color: white;border-radius: 0px 5px 0px 0px">Descrição</th>
								</tr>
							</thead>
							<tbody style="background-color: var(--table-itens)">
								<tr>
									<td>!extrato</td>
									<td>Ver extrato bancário</td>
								</tr>
								<tr>
									<td>!depositar</td>
									<td>Depositar dinheiro no banco
									</td>
								</tr>
								<tr>
									<td>!sacar</td>
									<td>Sacar dinheiro do banco
									</td>
								</tr>
								<tr>
									<td>!transferir</td>
									<td>Transferir dinheiro do banco para outro membro
									</td>
								</tr>
								<tr>
									<td>!pagar</td>
									<td>Pagar um membro com o dinheiro de sua carteira
									</td>
								</tr>
							</tbody>
							<thead>
								<tr>
									<th style="background-color: var(--primary-color);color: white;"></th>
									<th style="background-color: var(--primary-color);color: white;"></th>
								</tr>
							</thead>
							<tbody style="background-color: var(--table-itens)">

								<tr>
									<td>!inv</td>
									<td>Ver inventário
									</td>
								</tr>
								<tr>
									<td>!use</td>
									<td>Usar item de seu inventário
									</td>
								</tr>
								<tr>
									<td>!loja</td>
									<td>Ver itens da loja
									</td>
								</tr>
								<tr>
									<td>!item_info</td>
									<td>Ver informações de um item
									</td>
								</tr>
								<tr>
									<td>!vender_item</td>
									<td>Vender um item/carro seu para outro membro
									</td>
								</tr>
								<tr>
									<td>!saquear</td>
									<td>Rouba todo dinheiro da carteira e itens do inv de outro membro
									</td>
								</tr>
							</tbody>
							<thead>
								<tr>
									<th style="background-color: var(--primary-color);color: white;"></th>
									<th style="background-color: var(--primary-color);color: white;"></th>
								</tr>
							</thead>
							<tbody style="background-color: var(--table-itens)">
								<tr style="border-bottom-color: white;">
									<td>!garagem</td>
									<td>Ver garagem
									</td>
								</tr>
								<tr style="border-bottom-color: white;">
									<td>!carros</td>
									<td>Ver carros da concessionária
									</td>
								</tr>
								<tr style="border-bottom-color: white;">
									<td>!car_info</td>
									<td>Ver informações de um carro
									</td>
								</tr>
								<tr style="border-bottom-color: white;">
									<td>!consultar</td>
									<td>Consultar uma placa/membro para verificar seus veículos
									</td>
								</tr>
							</tbody>
						</table>
						<a style="text-decoration: none;" href="<?php echo INCLUDE_PATH; ?>listaComandos">
							<div class="ver-mais">
								Ver Mais
							</div>
						</a>
					</div>
				</div>
			</div>
		</div>

		<div class="border-green col-12 px-0 py-1 box_newsletter sugestao align-middle align-items-center d-flex justify-content-center text-center">
			<form class="col-12 p-0 " method="post" id="form_newsletter">
				<div class="row m-0">
					<div class="col-lg-3 col-md-6 ">
						<label style="margin-top: 12px;"><b>Envie sua sugestão</b></label>
					</div>
					<div class="col-lg-3 col-md-6 ">
						<?php
						if (Login::logado() == true) {
							echo '<input type="text" name="nickdiscord" value="' . $_SESSION['user'] . ' <@' . $_SESSION['id'] . '>" class="field" readonly>';
						} else {
							echo '<input type="text" name="nickdiscord" value="" placeholder="nick discord" class="field">';
						}
						?>
					</div>
					<div class="col-lg-3 col-md-6 ">
						<input type="text" name="sugestao" value="" placeholder="sua sugestão" class="field">
					</div>
					<div class="col-lg-3 col-md-6 ">
						<input type="submit" name="enviarsugestao" value="Enviar">
					</div>
				</div>
			</form>
		</div>

		<footer>
			<div class="align-items-center flex-column d-flex justify-content-center">
				<img style="width: 80px;" src="<?= INCLUDE_PATH ?>assets/logo.png" class="img-responsive">
				<div class="copy-right">Copyright 2022 © Todos os direitos reservados</div>
			</div>
		</footer>
	</div>
	<script src="<?php echo INCLUDE_PATH ?>resources/js/jquery.js"></script>
	<script src="<?php echo INCLUDE_PATH ?>resources/bootstrap/bootstrap.min.js"></script>
	<script src="<?php echo INCLUDE_PATH ?>resources/js/script.js"></script>
	<script src="<?php echo INCLUDE_PATH ?>resources/fontawesome/all.min.js"></script>
</body>

</html>
<?php ob_end_flush(); ?>