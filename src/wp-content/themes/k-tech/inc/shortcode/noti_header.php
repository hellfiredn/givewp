<?php

function noti_header_src($atts) {
    ob_start();

    if (have_rows('desc_list', 'option')) : ?>
        <section class="sec-header-desc">
            <div class="swiper descSwiper">
                <div class="swiper-wrapper">
                    <?php while (have_rows('desc_list', 'option')) : the_row(); 
                        $content = get_sub_field('desc_content'); ?>
                        <div class="swiper-slide">
                            <div class="slide-content text-center">
                                <?php echo $content; ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>

                <div class="swiper-button-next"></div>
                <div class="swiper-button-prev"></div>
                <div class="swiper-pagination"></div>
            </div>
        </section>

        <style>
        .descSwiper {
            width: 100%;
            max-width: 350px;
            position: relative;
        }
        .slide-content {
            font-size: 1rem;
        }
        .swiper-button-next,
        .swiper-button-prev {
            color: #000;
            z-index: 10;
        }
        #top-bar .html_topbar_left,
        #top-bar .flex-col:nth-child(2),
        #top-bar .flex-col:nth-child(4),
        #top-bar .swiper {
            width: 100%;
            max-width: 100%;
        }
        </style>
    <?php endif;

    return ob_get_clean();
}
add_shortcode('noti_header', 'noti_header_src');