
<article class="blog_article">
    <a title="<?= $post->title; ?>" href="<?= url("/blog/{$post->uri}"); ?>">
        <img title="<?= $post->title; ?>" alt="<?= $post->title; ?>" src="<?= image($post->cover, "600", "340"); ?>"/>
    </a>
    <header>
        <p class="meta"><?= $post->category()->title; ?> 
        &bull; <?= "{$post->author()->first_name} {$post->author()->last_name}" ?>
        &bull; <?= date_fmt($post->post_at)?></p>
        <h2><a title="<?= $post->title; ?>" href="<?= url("/blog/{$post->uri}"); ?>"><?= $post->title; ?></a></h2>
        <p><a title="<?= $post->title; ?>" href="<?= url("/blog/{$post->uri}"); ?>"><?= str_limit_chars($post->subtitle, 120); ?></a></p>
    </header>
</article>