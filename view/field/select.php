<select name="<?=esc_attr($this->getKey()); if ($this->getConfigData('multiple') === true) echo '[]'; ?>"
       class="<?=$this->getConfigData('searchable') === true ? 'search' : '';?>"
       <?=$this->getConfigData('multiple') === true ? ' multiple' : '';?>>
    <?php foreach ($this->getOptions() as $option) :
        if (is_object($option)) {
            $val = $option->value;
            $label = $option->label;
        } else {
            $val = $option;
            $label = $option;
        }
    ?>
        <option value="<?=esc_attr($val);?>"<?php if ($this->isSelected($val)) echo ' selected="selected"';?>><?=esc_html($label);?></option>
    <?php endforeach; ?>
</select>
