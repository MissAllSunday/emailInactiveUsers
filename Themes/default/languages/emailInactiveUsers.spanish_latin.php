<?php

/*
 * email Inactive Users
 *
 * @package eiu mod
 * @version 1.1
 * @author Jessica González <suki@missallsunday.com>
 * @copyright Copyright (c) 2014 Jessica González
 * @license http://www.mozilla.org/MPL/2.0/
 */

global $txt;

$txt['eiu_enable'] = 'Activar la modificaci&oacute;n';
$txt['eiu_enable_sub'] = 'El control maestro, necesita estar activdo para que el mod funcione correctamente.';
$txt['eiu_disable_removal'] = 'Desactivar la la opción de remover usuarios';
$txt['eiu_disable_removal_sub'] = 'Si se activa, el mod s&oacute;lo mandar&aacute; correos pero no borrar&aacute; ning&uacute;n usuario, al terminar su periodo de gracia y si el usuario no ha iniciado sesi&oacute;n, el mod volver&aacute; a marcar a el usuario para volver a enviar otro correo.';
$txt['eiu_title'] = 'Enviar un correo a los usuarios inactivos';
$txt['eiu_general'] = 'Configuraci&oacute;n principal';
$txt['eiu_list'] = 'Lista de usuarios';
$txt['eiu_list_title'] = 'Uusarios marcados para ser removidos';
$txt['eiu_list_title_potential'] = 'Usuarios marcados como posibles usuarios para ser removidos';
$txt['eiu_list_will_be_deleted'] = 'Ser&aacute; removido(a)';
$txt['eiu_list_noUsers'] = 'No hay ning&uacute;n usuario marcado como posible usuario ha ser removido';
$txt['eiu_list_name'] = 'Nombre';
$txt['eiu_list_login'] = '&uacute;ltima vez activo';
$txt['eiu_list_mail'] = 'correo enviado';
$txt['eiu_list_delete'] = 'Marcar para ser removido';
$txt['eiu_list_dont_delete'] = 'No borrar';
$txt['eiu_list_posts'] = 'Mensajes';
$txt['eiu_list_send'] = 'Enviar';
$txt['eiu_deleted'] = 'Los usuarios se han marcado para ser removidos con exito, la proxima vez que la tarea programada sea executada, los usurios ser&aacute;n removidos.';
$txt['eiu_dont'] = 'Los usuarios se han marcado como "intocables" con exito, la modificaci&oacute;n no volver&aacute; a marcar estos usuarios para ser removidos.';
$txt['eiu_desc'] = 'Este es tu email inactive users mod. p&aacute;nel de control para el mod email inactive users';
$txt['eiu_inactiveFor'] = 'Cuantos d&iacute;as desde la &uacute;ltima vez activo para mandar el correo.';
$txt['eiu_inactiveFor_sub'] = 'Si no se especifica un valor, se usar&aacute; el valor por defecto: 15';
$txt['eiu_sinceMail'] = 'Cuantos d&iacute;as se debe de esperar desde que el usuario fu&eacute; marcado para ser removido';
$txt['eiu_sinceMail_sub'] = 'En d&iacute;as, se empieza a contar a partir de que el administrador ha marcado en usuario para ser removido, si no se especifica ning&uacute;n valor, se usar&aacute; el valor por defecto: 15';
$txt['eiu_groups'] = 'Selecciona los grupos que ser&aacute;n affectados por este mod:';
$txt['eiu_groups_sub'] = 'Es una caja de selecci&oacute;n multiple, s&oacute;lo los usuario que tengan los grupos seleccionados como grupo primario o secundario ser&aacute;n elegibles para ser removidos, por defecto, el grupo administrador (id 1) queda exento de ser elegible.';
$txt['eiu_message'] = 'El cuerpo del mensaje a enviar';
$txt['eiu_message_sub'] = 'Puedes usar los siguientes comod&iacute;nes:<br />
	{user_name} se convertir&aacute; en el nombre real del usuario real name<br />
	{display_name} se convertir&aacute; en el nombre a motrar del usuario.<br />
	{last_login} mostrar&aacute; la &uacute;ltima vez que inici&oacute; sesi&oacute;n.<br />
	{forum_name} se convertir&aacute; en el nombre de tu foro<br />
	{forum_url} mostrar&aacute; la direcci&oacute;n web de tu foro<br />
	Si no se especifica ning&uacute;n mensaje, el mod usar&aacute; un mensaje predeterminado.';
$txt['eiu_subject'] = 'El asunto del mensaje';
$txt['eiu_subject_sub'] = 'No se puede usar HTML aqu&iacute;, si no se especifica ning&uacute;n valor, se usar&aacute; un valor predeterminado.';
$txt['eiu_posts'] = 'Mensajes:';
$txt['eiu_posts_sub'] = 'El n&uacute;mero de mensajes m&iacute;nimo que el usuario debe tener para ser tomado en cuenta para ser removido, si el/la usuario(a) tiene m&aacute;s mensajes que el n&uacute;mero establecido, no ser&aacute; tomado(a) en cuenta por el mod para ser removido(a).<br />Si se deja vacio, el mod usar&aacute; el valor predeterminado: 5.';
$txt['eiu_custom_message'] = 'Hola {user_name} te hemos extra&ntilde;ado en {forum_url} y nos gustar&iacute;a que regresaras con nosotros.';
$txt['eiu_custom_subject'] = 'Saludos desde {forum_name}.';
