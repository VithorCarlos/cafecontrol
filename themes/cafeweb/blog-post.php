<?php $v->layout("_theme"); ?>

    <article class="post_page">
        <header class="post_page_header">
            <div class="post_page_hero">
                <h1><?= $post->title; ?></h1>
                <img class="post_page_cover" alt="" title="" src="<?= image($post->cover, 1280); ?>"/>
                <div class="post_page_meta">
                    <div class="author">
                        <div><img src="<?= image($post->author()->photo, 200); ?>"/></div>
                        <div class="name">Por: <?= "{$post->author()->first_name} {$post->author()->last_name}";?></div>
                    </div>
                    <div class="date">Dia <?= date_fmt($post->post_at); ?></div>
                </div>
            </div>
        </header>

        <div class="post_page_content">
            <div class="htmlchars">
                <h2><?= $post->subtitle; ?></h2>
                <?= $post->content?>
            </div>

            <aside class="social_share">
                <h3 class="social_share_title icon-heartbeat">Ajude seus amigos a controlar:</h3>
                <div class="social_share_medias">
                    <!--facebook-->
                    <div class="fb-share-button" data-href="<?= url("/post/$post->uri"); ?>" 
                        data-layout="button_count"
                        data-size="large"
                        data-mobile-iframe="true">
                <!--url q vai receber os dados de fato no face-->
                        <a target="_blank"
                           href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode(url("/post/{$post->uri}")); ?>"
                           class="fb-xfbml-parse-ignore">Compartilhar</a>
                    </div>

                    <!--twitter-->
                    <a href="https://twitter.com/share?ref_src=site" class="twitter-share-button" data-size="large"
                       data-text="<?= $post->title; ?>" data-url="<?= url("/post/{$post->uri}"); ?>"
                       data-via="<?= str_replace("@", "", CONF_SOCIAL_TWITTER_CREATOR); ?>"
                       data-show-count="true">Tweet</a>
                </div>
            </aside>
        </div>

        <?php if(!empty($related)):?>
        <div class="post_page_related content">
            <section>
                <header class="post_page_related_header">
                    <h4>Veja também:</h4>
                    <p>Confira mais artigos relacionados e obtenha ainda mais dicas de controle para suas contas.</p>
                </header>

                <div class="blog_articles">
                    <?php foreach($related as $more): ?>
                        <?php $v->insert("blog-list", ["post" => $more]); ?>
                    <?php endforeach; ?>
                </div>
            </section>
        </div>
        <?php endif; ?>
    </article>


<?php $v->start("scripts"); ?>
<div id="fb-root"></div>
<script>(function (d, s, id) {
        var js, fjs = d.getElementsByTagName(s)[0];
        if (d.getElementById(id)) return;
        js = d.createElement(s);
        js.id = id;
        js.src = 'https://connect.facebook.net/pt_BR/sdk.js#xfbml=1&version=v3.1';
        fjs.parentNode.insertBefore(js, fjs);
    }(document, 'script', 'facebook-jssdk'));</script>

<script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>
<?php $v->end(); ?>