<?php

function share_social()
{

    ob_start();
    $social = get_field('social', 'option');
    ?>

    <ul class="share_social default_ul">
        <?php foreach ($social as $key => $item) {
            ?>
            <li><a href="<?php echo $item['link'] ?>">
                    <?php echo wp_get_attachment_image($item['icon']['ID'], 'full', "", array("class" => "")); ?>


                </a></li>
            <?php
        } ?>
    </ul>
    <?php

    return ob_get_clean();

}


add_shortcode('share_social', 'share_social');
