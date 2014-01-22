<?php $this->load->view('errors'); ?>

<div id="my_accordion">
<h3  class="accordion">Flagged Comments</h3>
<div id="flagged_comments">
	<?php 
	
	if(count($flagged_comments) > 0): 

		$this->table->clear();
		$this->table->set_template($cp_pad_table_template);
		$this->table->set_heading(
			lang('name'),
			lang('entry_title'),
			lang('total_flags'),
			lang('first_flag'),
			lang('last_flag')
		);
		
		foreach($flagged_comments as $option)
		{
			$this->table->add_row(
				'<!-- '.strip_tags($option['comment']).'--><a href="'.$url_base.'view_comment_flags'.AMP.'comment_id='.$option['comment_id'].'">'.word_limiter(strip_tags($option['comment']), 8).'</a>',
				'<a href="'.BASE.'&C=content_publish&M=entry_form&channel_id='.$option['channel_id'].'&entry_id='.$option['entry_id'].'">'.$option['entry_title'].'</a>',
				$option['total_flags'],
				m62_convert_timestamp($option['first_flag']),
				'<!-- '.strtotime($option['last_flag']).' -->'.m62_convert_timestamp($option['last_flag'])
			);
		}
		
		echo $this->table->generate();	
	else: 
		echo lang('no_flagged_comments'); 
	endif; ?>	
</div>

<h3  class="accordion">Flagged Entries</h3>
<div id="flagged_entries">
	<?php 
	
	if(count($flagged_entries) > 0): 

		$this->table->clear();
		$this->table->set_template($cp_pad_table_template);
		$this->table->set_heading(
			lang('name'),
			lang('total_flags'),
			lang('first_flag'),
			lang('last_flag')
		);
		
		foreach($flagged_entries as $option)
		{
			$this->table->add_row(
									'<!-- '.$option['title'].'--><a href="'.$url_base.'view_entry_flags'.AMP.'entry_id='.$option['entry_id'].'">'.$option['title'].'</a>',
									$option['total_flags'],
									m62_convert_timestamp($option['first_flag']),
									'<!-- '.strtotime($option['last_flag']).' -->'.m62_convert_timestamp($option['last_flag'])
									);
		}
		
		echo $this->table->generate();	
	else: 
		echo lang('no_flagged_entries'); 
	endif; ?>	
</div>
	<?php //echo form_close()?>		
</div>