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

$txt['eiu_enable'] = 'Activar la modificación';
$txt['eiu_enable_sub'] = 'El control maestro, necesita estar activdo para que el mod funcione correctamente.';
$txt['eiu_disable_removal'] = 'Desactivar la la opción de remover usuarios';
$txt['eiu_disable_removal_sub'] = 'Si se activa, el mod sólo mandará correos pero no borrará ningún usuario, al terminar su periodo de gracia y si el usuario no ha iniciado sesión, el mod volverá a marcar a el usuario para volver a enviar otro correo.';
$txt['eiu_title'] = 'Enviar un correo a los usuarios inactivos';
$txt['eiu_general'] = 'Configuración principal';
$txt['eiu_list'] = 'Lista de usuarios';
$txt['eiu_list_title'] = 'Uusarios marcados para ser removidos';
$txt['eiu_list_title_potential'] = 'Usuarios marcados como posibles usuarios para ser removidos';
$txt['eiu_list_will_be_deleted'] = 'Será removido(a)';
$txt['eiu_list_noUsers'] = 'No hay ningún usuario marcado como posible usuario ha ser removido';
$txt['eiu_list_name'] = 'Nombre';
$txt['eiu_list_login'] = 'Última vez activo';
$txt['eiu_list_mail'] = 'correo enviado';
$txt['eiu_list_delete'] = 'Marcar para ser removido';
$txt['eiu_list_dont_delete'] = 'No borrar';
$txt['eiu_list_posts'] = 'Mensajes';
$txt['eiu_list_send'] = 'Enviar';
$txt['eiu_deleted'] = 'Los usuarios se han marcado para ser removidos con exito, la proxima vez que la tarea programada sea executada, los usurios serán removidos.';
$txt['eiu_dont'] = 'Los usuarios se han marcado como "intocables" con exito, la modificación no volverá a marcar estos usuarios para ser removidos.';
$txt['eiu_desc'] = 'Este es tu email inactive users mod. pánel de control para el mod email inactive users';
$txt['eiu_inactiveFor'] = 'Cuantos días desde la última vez activo para mandar el correo.';
$txt['eiu_inactiveFor_sub'] = 'Si no se especifica un valor, se usará el valor por defecto: 15';
$txt['eiu_sinceMail'] = 'Cuantos días se debe de esperar desde que el usuario fué marcado para ser removido';
$txt['eiu_sinceMail_sub'] = 'En días, se empieza a contar a partir de que el administrador ha marcado en usuario para ser removido, si no se especifica ningún valor, se usará el valor por defecto: 15';
$txt['eiu_groups'] = 'Selecciona los grupos que serán affectados por este mod:';
$txt['eiu_groups_sub'] = 'Es una caja de selección multiple, sólo los usuario que tengan los grupos seleccionados como grupo primario o secundario serán elegibles para ser removidos, por defecto, el grupo administrador (id 1) queda exento de ser elegible.';
$txt['eiu_message'] = 'El cuerpo del mensaje a enviar';
$txt['eiu_message_sub'] = 'Puedes usar los siguientes comodínes:<br />
	{user_name} se convertirá en el nombre real del usuario real name<br />
	{display_name} se convertirá en el nombre a motrar del usuario.<br />
	{last_login} mostrará la última vez que inició sesión.<br />
	{forum_name} se convertirá en el nombre de tu foro<br />
	{forum_url} mostrará la dirección web de tu foro<br />
	Si no se especifica ningún mensaje, el mod usará un mensaje predeterminado.';
$txt['eiu_subject'] = 'El asunto del mensaje';
$txt['eiu_subject_sub'] = 'No se puede usar HTML aquí, si no se especifica ningún valor, se usará un valor predeterminado.';
$txt['eiu_posts'] = 'Mensajes:';
$txt['eiu_posts_sub'] = 'El número de mensajes mínimo que el usuario debe tener para ser tomado en cuenta para ser removido, si el/la usuario(a) tiene más mensajes que el número establecido, no será tomado(a) en cuenta por el mod para ser removido(a).<br />Si se deja vacio, el mod usará el valor predeterminado: 5.';
$txt['eiu_custom_message'] = 'Hola {user_name} te hemos extrañado en {forum_url} y nos gustaría que regresaras con nosotros.';
$txt['eiu_custom_subject'] = 'Saludos desde {forum_name}.';
