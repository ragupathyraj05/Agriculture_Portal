<?php include 'includes/header.php'; ?>

<div class="container mt-5">
    <div class="glass-card">
        <h2 class="text-center mb-5">Agriculture News</h2>
        
        <?php
        $url = "https://newsapi.org/v2/everything?q=agriculture&sortBy=publishedAt&apiKey=" . NEWS_API_KEY;
        
        // Use curl instead of file_get_contents for better reliability
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'AgriculturePortal/1.0'); // NewsAPI requires User-Agent
        $response = curl_exec($ch);
        curl_close($ch);
        
        $newsdata = json_decode($response);
        ?>

        <div class="news-grid">
            <?php if(isset($newsdata->articles)): ?>
                <?php foreach($newsdata->articles as $news): ?>
                    <?php if($news->urlToImage): ?>
                    <div class="news-item" style="border-bottom: 1px solid #eee; padding: 20px 0; display: flex; gap: 20px;">
                        <div class="news-image" style="flex: 0 0 150px;">
                            <img src="<?php echo $news->urlToImage; ?>" alt="News Thumbnail" style="width: 100%; border-radius: 5px;">
                        </div>
                        <div class="news-content">
                            <h4><?php echo $news->title; ?></h4>
                            <p class="text-muted"><small>By <?php echo $news->author ? $news->author : 'Unknown'; ?> | <?php echo date('F j, Y', strtotime($news->publishedAt)); ?></small></p>
                            <p><?php echo $news->description; ?></p>
                            <a href="<?php echo $news->url; ?>" target="_blank" class="btn-primary" style="padding: 5px 15px; font-size: 0.9rem;">Read More</a>
                        </div>
                    </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-center">Unable to fetch news. Please check API key.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
