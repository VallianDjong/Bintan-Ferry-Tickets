<?php
require './assets/includes/deals-data.php';

$slug = isset($_GET['slug']) ? $_GET['slug'] : '';

// If the slug is missing or invalid, send them back to the deals list
if ($slug === '' || !isset($deals[$slug])) {
    header('Location: promotion.php');
    exit;
}

$deal = $deals[$slug];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />
    <title><?= htmlspecialchars($deal['title']) ?> | VTL Travel</title>
    <meta name="description" content="<?= htmlspecialchars($deal['short_desc']) ?>" />


    <!-- All Plugins CSS -->
    <link rel="stylesheet" href="./assets/css/plugins/bootstrap.min.css" />
    <link rel="stylesheet" href="./assets/css/plugins/aos.css" />
    <link rel="stylesheet" href="./assets/css/plugins/nice-select.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet" />

    <!-- Custom Css -->
    <link rel="stylesheet" href="./assets/css/helper.css" />
    <link rel="stylesheet" href="./assets/css/style.css" />
    <link rel="stylesheet" href="./assets/css/responsive.css" />

    <script src="https://cdn.jsdelivr.net/npm/@emailjs/browser@3/dist/email.min.js"></script>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
</head>

<body>
   <?php require './assets/includes/navbar.php'; ?>

    <main class=" main-body">

        <!-- Promo Banner -->

        <!-- START: Promo Hero Section Container -->
        <section class="customContainer">
            <div class="promo-hero-wrapper">

                <!-- Top Breadcrumbs Path Navigation -->
                <nav class="promo-breadcrumbs">
                    <a href="#" class="crumb-link">Home</a>
                    <span class="crumb-separator">/</span>
                    <a href="#" class="crumb-link">Deals</a>
                    <span class="crumb-separator">/</span>
                    <span class="crumb-link active"><?= htmlspecialchars($deal['title']) ?></span>
                </nav>

                <!-- Main Banner Image Billboard with Overlays -->
               <div class="promo-banner-card" style="background-image: url('<?= $deal['banner_image'] ?>');">
                    <!-- <div class="banner-overlay-gradient"></div> -->



                    <!-- Center Main Content Grid (Splits Title/Metadata left and Pricing right) -->
                    <div class="banner-middle-content">




                    </div>

                    <!-- Bottom Content Layer (Booking Info Left & Corporate Badge Right) -->


                </div>

            </div>
        </section>
        <!-- END: Promo Hero Section Container -->



        <!-- START: Promotion Details Section -->
        <section class="customContainer">
            <div class="promo-detail-container">

                <!-- Left Column: Primary Content and Details -->
               <div class="promo-detail-main">
    <h1 class="promo-detail-title"><?= htmlspecialchars($deal['title']) ?></h1>

    <div class="promo-detail-body">
        <?php foreach ($deal['body'] as $para): ?>
            <p><?= htmlspecialchars($para) ?></p>
        <?php endforeach; ?>
    </div>

    <div class="promo-detail-section-block">
        <h3 class="promo-block-heading">Promo Details & Inclusions</h3>
        <ul class="promo-bullets">
            <?php foreach ($deal['inclusions'] as $item): ?>
                <li><?= htmlspecialchars($item) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>

    <div class="promo-detail-section-block">
        <h3 class="promo-block-heading">Pricing & Reservations</h3>
        <ul class="promo-bullets">
            <li>Price: <?= htmlspecialchars($deal['price']) ?></li>
            <li>Book Now via WhatsApp/Call:
                <a href="tel:<?= $deal['phone'] ?>" class="promo-inline-link"><?= $deal['phone_label'] ?></a>
            </li>
        </ul>
    </div>
</div>

                <!-- Right Column: Validity Widget and Booking Action -->
                <div class="promo-detail-sidebar">
                    <h3 class="validity-heading">Promotion Validity</h3>

                    <div class="validity-badge-row">
                        <!-- Start Date Box -->
                        <div class="validity-box">
                            <span class="validity-label">Start Date</span>
                            <span class="validity-date"><?= $deal['start_date'] ?></span>
                        </div>
                        <!-- End Date Box -->
                        <div class="validity-box">
                            <span class="validity-label">End Date</span>
                            <span class="validity-date"><?= $deal['end_date'] ?></span>
                        </div>
                    </div>

                    <a href="book-now.php" class="promo-book-now-btn">Book Now</a>
                </div>

            </div>
        </section>
        <!-- END: Promotion Details Section -->

        <!-- gallary section -->

        <section class="customContainer">
            <div class="gallery-section">

                <h2 class="gallery-main-title">Gallery</h2>

                <div class="gallery-grid">
    <?php foreach ($deal['gallery'] as $img): ?>
    <div class="gallery-card">
        <img src="<?= $img ?>" alt="<?= htmlspecialchars($deal['title']) ?>" class="gallery-img">
    </div>
    <?php endforeach; ?>
</div>

            </div>
        </section>


        <!-- more deals -->

        <section class="customContainer">
            <div class="more-deals-section">

                <h2 class="more-deals-main-title">More Deals In Bintan Resorts</h2>

                <div class="more-deals-carousel-wrapper">
                    <div class="swiper-button-prev-deals">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="19" y1="12" x2="5" y2="12"></line>
                            <polyline points="12 19 5 12 12 5"></polyline>
                        </svg>
                    </div>
                    <div class="swiper-button-next-deals">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                            <polyline points="12 5 19 12 12 19"></polyline>
                        </svg>
                    </div>

                    <div class="swiper moreDealsSwiper">
                       <div class="swiper-wrapper">
    <?php foreach ($deals as $otherSlug => $other): ?>
        <?php if ($otherSlug === $slug) continue; // skip the one being viewed ?>
        <div class="swiper-slide">
            <a href="details.php?slug=<?= urlencode($otherSlug) ?>" style="text-decoration:none;color:inherit;">
                <article class="deal-card">
                    <div class="deal-img-wrapper">
                        <img src="<?= $other['card_image'] ?>" alt="<?= htmlspecialchars($other['title']) ?>">
                    </div>
                    <div class="deal-card-content">
                        <div class="deal-date-tag">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                <line x1="16" y1="2" x2="16" y2="6"></line>
                                <line x1="8" y1="2" x2="8" y2="6"></line>
                                <line x1="3" y1="10" x2="21" y2="10"></line>
                            </svg>
                            <span>Valid Till <?= $other['valid_till'] ?></span>
                        </div>
                        <h3 class="deal-card-title"><?= htmlspecialchars($other['title']) ?></h3>
                    </div>
                </article>
            </a>
        </div>
    <?php endforeach; ?>
</div>
                    </div>
                </div>

            </div>
        </section>



    </main>

    <div class="vtl-separator"></div>


<?php require './assets/includes/footer.php'; ?>

    <!-- Javascript Links -->
    <script src="./assets/js/jquery-3.7.1.min.js"></script>
    <script src="./assets/js/plugins.js"></script>
    <script src="./assets/js/main.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
</body>

</html>