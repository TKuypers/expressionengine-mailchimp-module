<div class="box">
	<div class="tbl-ctrls">
		<fieldset class="tbl-search right">
			<a class="btn tn action" href="<?=ee('CP/URL')->make('addons/settings/exp_mailchimp/settings')?>"><?=lang('settings')?></a>
		</fieldset>
		<h1><?=lang('lists')?></h1>

		<? if(isset($error)): ?>
			
			<span class="invalid">
				<span class="setting-field">
					<em class="ee-form-error-message"><?=$error?></em>
				</span>
			</span>

		<? else: ?>

			<? if(!isset($data) || count($data) == 0):?>

			<span class="invalid">
				<span class="setting-field">
					<em class="ee-form-error-message"><?=lang('no_lists_found');?></em>
				</span>
			</span>

			<? else: ?>

			<div class="tbl-wrap pb">
				<table cellspacing="0" id="lists" class=" grid-input-form" >
					<thead>
						<tr>
							<th><?=lang('name')?></th>
							<th><?=lang('id')?></th>
							<th><?=lang('subscribers')?></th>
						</tr>
					</thead>
					<tbody>
						<? foreach($data as $row): ?>
						<tr>
							<? foreach($row as $column): ?>
								<td><?=$column?></td>
							<? endforeach; ?>
						</tr>
					<? endforeach; ?>
					</tbody>
				</table>
			</div>

			<? endif; ?>

		<? endif; ?>
	</div>
</div>
