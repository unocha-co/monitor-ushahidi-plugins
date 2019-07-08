<div id="av_plugin" class="hide">
    <div id="av_note">
        <h4>
            <?php echo Kohana::lang('av.actors')?>&nbsp;-&nbsp;
            <?php echo Kohana::lang('av.victims')?>
        </h4>
        <p class="note"><?php echo Kohana::lang('av.use_note')?></p>
        <div id="av_cats">
            <?php
            echo form::label('av_category_id', Kohana::lang('ui_main.category'));
            echo form::dropdown('av_category_id', array('0' => Kohana::lang('av.av_category')));
            ?>
        </div>
    </div>
    <div id="av">
        <ul>
            <li><a href="#div_actor"><?php echo Kohana::lang('av.actor')?></a></li>
            <li><a href="#div_victim"><?php echo Kohana::lang('av.victims')?></a></li>
        </ul>
        <div id="div_actor" class="av">
            <div id="div_actor_content">
            <?php 
                echo avh::aform();
            ?>    
            </div>
        </div>
        <div id="div_victim" class="av">
            <div id="div_victim_content">
            <?php 
                echo avh::vform();
            ?>    
            </div>
        </div>
    </div>
</div>
