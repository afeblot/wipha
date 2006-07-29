<span class="error">
{if $error == NO_SUCH_USER} This user doesn't exist
{elseif $error == USER_EXISTS} This user already exists
{elseif $error == BAD_USER_NAME} Bad user name. Only alphanum characters allowed
{elseif $error == LOGIN_PASSWD_REQ} User name and password required
{elseif $error == DEMO_ADMIN} Can't modify 'admin' login in Demo mode
{elseif $error == DEMO_GUEST} Can't modify 'guest' settings in Demo mode
{elseif $error == NO_SUCH_LIB} This library doesn't exist {* this is't an error! *}
{/if}
</span>
