<link href='cache.php?css=theme,default,alerts' rel="stylesheet" />

<style>
    div#db_objects { margin-top:5px;padding:3px;overflow:auto;height:330px;width:95%;border:3px double #efefef }
    div.objhead     { background-color:#ececec; padding: 5px; margin: 0 0 3px 0 }
    span.toggler    { display:inline-block; float:right; cursor: pointer; font-size:16px; margin: -5px 0 0 0 }
    div.obj         { padding:5px; margin:0 0 0 20px }
</style>

<div id="popup_wrapper">
    <div id="popup_contents">
            <table border="0" cellpadding="5" cellspacing="4" style="width: 100%;height:100%">
            <tr>
            <td align="left" valign="top" width="45%">
                <fieldset id="object_types">
                    <legend><?php echo __('Object Types'); ?></legend>
                    <table border="0" cellspacing="10" cellpadding="5" width="100%">
                        <tr><td valign="top">
                        <input type='checkbox' name='object_type[]' value="functions" id='ddl_functions' checked="checked" /><label class="right" for='ddl_functions'><?php echo __('Function definitions'); ?></label>
                        </td></tr>
                        <tr><td valign="top">
                        <input type='checkbox' name='object_type[]' value="triggers" id='ddl_triggers' /><label class="right" for='ddl_triggers'><?php echo __('Trigger definitions'); ?></label>
                        </td></tr>
                        <tr><td valign="top">
                        <input type='checkbox' name='object_type[]' value="views" id='ddl_views' /><label class="right" for='ddl_views'><?php echo __('View definitions'); ?></label>
                        </td></tr>
                        <tr><td valign="top">
                        <input type='checkbox' name='object_type[]' value="tables" id='ddl_tables' /><label class="right" for='ddl_tables'><?php echo __('Table definitions'); ?></label>
                        </td></tr>
                    </table>
            </fieldset>
            </td>

            <td align="left" valign="top" width="55%">
            <fieldset>
                <legend><?php echo __('Search Options'); ?></legend>
                <table border="0" cellspacing="10" cellpadding="5" width="100%">
                <tr><td valign="top">
                <label><?php echo __('Text to search'); ?>:</label><input type='text' name='keyword' id='keyword' />
                </td></tr>
                </table>
            </fieldset>

            </td>
            </tr>
            </table>
    </div>
    <div id="popup_footer">
        <div id="popup_buttons">
            <input type='button' id="btn_search" value='<?php echo __('Search'); ?>' />
        </div>
    </div>
</div>

<script type="text/javascript" language='javascript' src="cache.php?script=common,jquery,ui,query,options,alerts"></script>
<script type="text/javascript" language="javascript">
window.title = "<?php echo __('Search in Database'); ?>";

function searchDatabase(event) {
    if ($('#keyword').val() === '') {
        jAlert(__('Enter the text to search in database'));
        return false;
    }
    if ($('#object_types').find(':checked').length === 0) {
        jAlert(__('No Object Type selected'));
        return false;
    }
    wrkfrmSubmit('schemasearch', '', '', '');
    return false;
}

$(function() {

    $('#btn_search').button().click(searchDatabase);
    $('#frmquery').submit(searchDatabase);
    $('#keyword').focus();
});
</script>
<?php
    echo getGeneratedJS();
?>