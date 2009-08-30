<span class="error">
{if $error == LIBNAME_EXISTS} This library name already exists
{elseif $error == LIBPATH_EXISTS} This library path already exists
{elseif $error == PATH_NAME_REQ} Library path and name required
{elseif $error == LIB_ABS_PATH} This path is not absolute (/Users/...)
{elseif $error == DEMO_LIB} Can't modify libraries in Demo mode
{/if}
</span>
