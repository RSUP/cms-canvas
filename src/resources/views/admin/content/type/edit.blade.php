@include('cmscanvas::admin.content.type.subnav')

<div class="box">
    <div class="heading">
        <h1><img alt="" src="{!! Theme::asset('images/layout.png') !!}">Content Type Edit - <?php echo $contentType->title; ?> (<?php echo $contentType->short_name; ?>)</h1>

        <div class="buttons">
            <a class="button" href="javascript:void(0);" id="save"><span>Save</span></a>
            <a class="button" href="javascript:void(0);" id="save_exit"><span>Save &amp; Exit</span></a>
            <a class="button" href="{!! Admin::url('content/type') !!}"><span>Cancel</span></a>
        </div>
    </div>
    <div class="content">

        {!! Form::model($contentType, array('id' => 'layout_edit')) !!}
        <div>
            <table class="list">
                <thead>
                    <tr>
                        <th width="220">Fields</th>
                        <th>Short Tag</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Entry Title</td>
                        <td>
                            @{!! $title !!}
                        </td>
                    </tr>
                    @foreach ($contentType->fields as $field)
                        <tr>
                            <td>{!! $field->label !!}</td>
                            <td>{!! $<?php echo $field->short_tag; ?> !!}</a></div></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div id="tabs">
                <ul class="htabs">
                    <li><a href="#html-tab">HTML</a></li>
                    <li><a href="#page-head-tab">Page &lt;head&gt;</a></li>
                    <li><a href="#settings-tab">Settings</a></li>
                    <li><a href="#permissions-tab">Permissions</a></li>
                </ul>

                <div id="html-tab">
                    {!! Form::textarea('layout', null, array('id' => 'layout')) !!}
                </div>

                <div id="page-head-tab">
                    <p class="info">Include custom JavaScript, CSS, and/or meta information in the <strong>&lt;head&gt;</strong> block of this content type's pages.</p>

                    {!! Form::textarea('page_head', null, array('id' => 'page_head')) !!}
                </div>

                <div id="settings-tab">
                    <div class="form">
                        <div>
                            <label for="title"><span class="required">*</span> Title:</label>
                            {!! Form::text('title') !!}
                        </div>
                        <div>
                            <label for="short_name"><span class="required">*</span> Short Name:</label>
                            {!! Form::text('short_name') !!}
                        </div>
                        <div>
                            <label for="theme_layout"><span class="required">*</span> Theme Layout:</label>
                            {!! Form::select('theme_layout', ['' => '-- None --'] + $themeLayouts) !!}
                        </div>
                        <div>
                            {!! HTML::decode(Form::label('route', 'Route:<span class="help">Optional route to render the content type directly without an entry.</span>')) !!}
                            {!! Form::text('route') !!}
                        </div>
                        <div>
                            {!! HTML::decode(Form::label('dynamic_routing_flag', 'Dynamic Routing: <span class="help">Dynamically generate routes for associated entries using the content type route and the entry\'s url title</span>')) !!}
                            <span>
                                <label>{!! Form::radio('dynamic_routing_flag', '1') !!} Enabled</label>
                                <label>{!! Form::radio('dynamic_routing_flag', '0', true) !!} Disabled</label>
                            </span>
                        </div>
                        <div>
                            {!! HTML::decode(Form::label('entries_allowed', 'Number of Entries Allowed:<span class="help">Number of entries allowed to be created with this content type.</span>')) !!}
                            {!! Form::text('entries_allowed', null, array('class' => 'short')) !!}
                            <span class="ex">Leave empty for unlimited</span>
                        </div>
                        <div>
                            {!! HTML::decode(Form::label('max_revisions', 'Max Revisions:<span class="help">Max number of revisions to store for each entry</span>')) !!}
                            {!! Form::text('entries_allowed', null, array('class' => 'short')) !!}
                            <span class="ex">No revisions will be kept if left empty</span>
                        </div>
                    </div>
                </div>

                <div id="permissions-tab">
                    <div class="form">
                        <fieldset>
                            <legend>Admin Entry Permissions</legend>
                            <div>
                                <label for="title">View:</label>
                                {!! Form::select('admin_entry_view_permission_id', ['' => '-- None --'] + $permissions->lists('name', 'id')->all()) !!}
                            </div>
                            <div>
                                <label for="title">Edit:</label>
                                {!! Form::select('admin_entry_edit_permission_id', ['' => '-- None --'] + $permissions->lists('name', 'id')->all()) !!}
                            </div>
                            <div>
                                <label for="title">Create:</label>
                                {!! Form::select('admin_entry_create_permission_id', ['' => '-- None --'] + $permissions->lists('name', 'id')->all()) !!}
                            </div>
                            <div>
                                <label for="title">Delete:</label>
                                {!! Form::select('admin_entry_delete_permission_id', ['' => '-- None --'] + $permissions->lists('name', 'id')->all()) !!}
                            </div>
                        </fieldset>
                    </div>
                </div>
            </div>
        </div>
        {!! Form::close() !!}
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        var editor = CodeMirror.fromTextArea(document.getElementById("layout"), {
            lineNumbers: true,
            matchBrackets: true,
            mode: "application/x-httpd-php",
            indentUnit: 4,
            indentWithTabs: true,
            enterMode: "keep",
            tabMode: "shift"
        });

        var editor = CodeMirror.fromTextArea(document.getElementById("page_head"), {
            lineNumbers: true,
            matchBrackets: true,
            mode: "application/x-httpd-php",
            indentUnit: 4,
            indentWithTabs: true,
            enterMode: "keep",
            tabMode: "shift"
        });

        $( "#tabs" ).tabs();

        // Save Content
        $("#save, #save_exit").click( function() {

            if ($(this).attr('id') == 'save_exit') {
                $('<input>').attr({
                    type: 'hidden',
                    name: 'save_exit',
                    value: '1'
                }).appendTo('#layout_edit');

                $('#layout_edit').submit();
            } else {
                $('#layout_edit').submit();
            }
        });
    });
</script>
