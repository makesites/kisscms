<div class="post"> 
    <div class="posttop"> 
        <a href="/2010/01/19/editor/" rel="bookmark" title="Permanent Link: Editor"> 
            <label> 
                <strong class="date"><?=date("d", $date)?></strong> <strong class="month"><?=date("M", $date)?></strong>
                <!--strong class="comments">0</strong--> 
            </label> 
        </a> 
    </div> 
    
    <!-- end of .posttop --> 
    <div class="postbody"> 
        <h2><?=$title?></h2>
        <p class="postinfo"> 
            Posted at <?=date("G:i", $date)?>
        </p> 
        <div><?=$content?></div>
    </div> 
    <!-- end of .postfoot --> 
    <div class="postmeta"> 
        <div class="right"> 
            <strong>Tags:</strong> <? Section::display("tags", $tags, "inline")?>
        </div> 
    </div> 
    <!-- end of .postmeta --> 
</div> 
<!-- end of .post --> 

