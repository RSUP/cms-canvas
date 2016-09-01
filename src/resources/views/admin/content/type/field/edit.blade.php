@include('cmscanvas::admin.content.type.subnav')

<div class="box">
    <div class="heading">
        <?php if ( ! empty($field)): ?>
            <h1><img alt="" src="{!! Theme::asset('images/layout.png') !!}">Edit Field - {!! $field->label !!}</h1>
        <?php else: ?>
            <h1><img alt="" src="{!! Theme::asset('images/layout.png') !!}">Add Field</h1>
        <?php endif; ?>

        <div class="buttons">
            <a class="button" href="javascript:void(0);" id="save" onClick="$('#form').submit();"><span>Save</span></a>
            <a class="button" href="{!! Admin::url('content/type/'.$contentType->id.'/field') !!}"><span>Cancel</span></a>
        </div>
    </div>
    <div class="content">

        @if ( ! empty($field))
            {!! Form::model($field, ['id' => 'form']) !!}
        @else
            {!! Form::open(['id' => 'form']) !!}
        @endif
        <div>
            <div class="form">
                <div>
                    {!! HTML::decode(Form::label('content_type_field_type_id', '<span class="required">*</span> Field Type:')) !!}
                    {!! Form::select('content_type_field_type_id', $fieldTypes->pluck('name', 'id')->all(), (empty($field) ? 1 : null)) !!}
                </div>
                <div>
                    {!! HTML::decode(Form::label('label', '<span class="required">*</span> Field Label:')) !!}
                    {!! Form::text('label') !!}
                </div>
                <div>
                    {!! HTML::decode(Form::label('short_tag', '<span class="required">*</span> Short Tag:')) !!}
                    {!! Form::text('short_tag') !!}
                </div>
                <div>
                    {!! HTML::decode(Form::label('required', '<span class="required">*</span> Require Field:')) !!}
                    <span>
                        <label>{!! Form::radio('required', '1') !!} Yes</label>
                        <label>{!! Form::radio('required', '0', true) !!} No</label>
                    </span>
                </div>
                <div>
                    {!! HTML::decode(Form::label('translate', '<span class="required">*</span> Translate Field:')) !!}
                    <span>
                        <label>{!! Form::radio('translate', '1') !!} Yes</label>
                        <label>{!! Form::radio('translate', '0', true) !!} No</label>
                    </span>
                </div>

                <span id="config">{!! $fieldTypeSettings !!}</span>
            </div>
        </div>
        {!! Form::close() !!}
    </div>
</div>

<script type="text/javascript">
    $(document).ready( function() {
        $('#content_type_field_type_id').change( function() {
            $.post(
                "{!! Admin::url('content/type/'.$contentType->id.'/field/settings') !!}", 
                { content_type_field_type_id: $('#content_type_field_type_id').val() {!! ( ! empty($field)) ? ', field_id: ' . $field->id : '' !!} }, 
                function(data) {
                    $('#config').html(data);
                }
            );
        });

        $('#content_type_field_type_id').trigger("change");

        @if (empty($field))
            $('#label').keyup( function(e) {
                $('#short_tag').val($(this).val().toLowerCase().replace(/\s+/g, '_').replace(/[^a-z0-9\-_]/g, ''))
            });
        @endif
    });
</script>