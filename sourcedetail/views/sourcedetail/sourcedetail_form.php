<div id="sourcedetail_plugin">
    <div id="av_note">
        <h4>
            <?php echo Kohana::lang('sourcedetail.title')?>
        </h4>
        <p class="note"><?php echo Kohana::lang('sourcedetail.use_note')?></p>
    </div>
    <div id="sourcedetail">
        <input type="hidden" id="sourcedetail_num" name="sourcedetail_num" value="0" />
        <?php 
            echo sourcedetailh::form();
        ?>    
    </div>
</div>
