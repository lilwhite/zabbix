<script type="text/x-jquery-tmpl" id="tag-row-tmpl">
	<?= (new CRow([
		(new CTextBox('tags[#{rowNum}][tag]'))
			->setAttribute('placeholder', _('tag'))
			->setWidth(ZBX_TEXTAREA_FILTER_SMALL_WIDTH),
		(new CRadioButtonList('tags[#{rowNum}][operator]', MAINTENANCE_TAG_OPERATOR_LIKE))
			->addValue(_('Contains'), MAINTENANCE_TAG_OPERATOR_LIKE)
			->addValue(_('Equals'), MAINTENANCE_TAG_OPERATOR_EQUAL)
			->setModern(true),
		(new CTextBox('tags[#{rowNum}][value]'))
			->setAttribute('placeholder', _('value'))
			->setWidth(ZBX_TEXTAREA_FILTER_SMALL_WIDTH),
		(new CCol(
			(new CButton('tags[#{rowNum}][remove]', _('Remove')))
				->addClass(ZBX_STYLE_BTN_LINK)
				->addClass('element-table-remove')
		))->addClass(ZBX_STYLE_NOWRAP)
	]))
		->addClass('form_row')
		->toString()
	?>
</script>

<script type="text/javascript">
	jQuery(function($) {
		$('#maintenance_type').change(function() {
			var maintenance_type = $('input[name=maintenance_type]:checked', $(this)).val();
			if (maintenance_type == <?= MAINTENANCE_TYPE_NODATA ?>) {
				$('#tags input, #tags button').prop('disabled', true);
				$('#tags input[name$="[tag]"], #tags input[name$="[value]"]').removeAttr('placeholder');
			}
			else {
				$('#tags input, #tags button').prop('disabled', false);
				$('#tags input[name$="[tag]"]').attr('placeholder', <?= CJs::encodeJson(_('tag')) ?>);
				$('#tags input[name$="[value]"]').attr('placeholder', <?= CJs::encodeJson(_('value')) ?>);
			}
		});

		$('#tags').dynamicRows({template: '#tag-row-tmpl'});

		// Maintenance periods.
		$('#maintenance_periods').on('click', '[data-action]', function() {
			var btn = $(this),
				rows = $('#maintenance_periods table > tbody > tr'),
				params;

			switch (btn.data('action')) {
				case 'remove':
					btn.closest('tr').remove();
					break;

				case 'edit':
					var row = btn.closest('tr');

					params = {
						update: 1,
						index: row.find('[type="hidden"]:first').attr('name').match(/\[(\d+)\]/)[1]
					};

					row.find('input[type="hidden"]').each(function() {
						var $input = $(this),
							name = $input.attr('name').match(/\[([^\]]+)]$/);

						if (name) {
							params[name[1]] = $input.val();
						}
					});

					PopUp("popup.maintenance.period", params, null, btn);
					break;

				case 'add':
					var index = 0;

					rows.each(function(row) {
						index = Math.max(index,
							parseInt($(this).find('[type="hidden"]:first').attr('name').match(/\[(\d+)\]/)[1])
						);
					});

					params = {
						index: index + 1
					}

					PopUp("popup.maintenance.period", params, null, btn);
					break;
			}
		});
	});
</script>
