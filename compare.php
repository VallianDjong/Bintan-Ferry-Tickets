<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />
    <meta name="description" content="Compare Economy Class and Emerald Class on Bintan Resort Ferries. Check differences in check-in, seating, baggage allowance, lounge access, and onboard services. Book your ferry today!" />
    <title>Economy vs Emerald Class - Batam Ferry Comparison | VTL Travel</title>


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
        <!-- 
        compare banner section -->

        <!-- START: Scenic Banner Section -->
        <section class="customContainer">
            <div class="scenic-banner-container">

                <!-- Link block container wrapper -->
                <a href="#" class="scenic-banner-link" aria-label="View Scenic Tours and Ferry Destinantions">
                    <div class="scenic-banner-frame">
                        <img src="assets/images/compareBanner.png"
                            alt="Ferry cruising on calm turquoise water with mountainous background"
                            class="scenic-banner-img">
                        <div class="scenic-banner-overlay"></div>
                    </div>
                </a>

            </div>
        </section>
        <!-- END: Scenic Banner Section -->

        <!-- table section -->
        <section class="customContainer">
            <div class="travel-class-section">

                <h1 class="travel-class-title">Class Of Travel</h1>

                <div class="travel-table-responsive">
                    <table class="travel-comparison-table">
                        <thead>
                            <tr>
                                <th class="th-economy">Economy Class</th>
                                <th class="th-emerald">Emerald Class</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Regular Check-in counter</td>
                                <td>Dedicated Check-in counter</td>
                            </tr>
                            <tr>
                                <td>Free seating</td>
                                <td>Pre-assigned seating</td>
                            </tr>
                            <tr>
                                <td>Baggage allowance of 20kg per person</td>
                                <td>Baggage allowance of 30kg per person *Includes priority baggage handling upon
                                    arrival</td>
                            </tr>
                            <tr>
                                <td class="cell-cross">&times;</td>
                                <td>Use of the Emerald Lounge (Tanah Merah) & Bintan Executive Lounge (Bintan)</td>
                            </tr>
                            <tr>
                                <td class="cell-cross">&times;</td>
                                <td>Use of the Emerald Lounge (Tanah Merah) & Bintan Executive Lounge (Bintan)</td>
                            </tr>
                            <tr>
                                <td class="cell-cross">&times;</td>
                                <td>Complimentary non-alcoholic beverages onboard</td>
                            </tr>
                            <tr>
                                <td class="cell-cross">&times;</td>
                                <td>Priority Boarding & Disembarkation</td>
                            </tr>
                            <tr>
                                <td class="cell-cross">&times;</td>
                                <td>Expedited Immigration Clearance *Applicable only on Arrival and Departure in Bintan
                                </td>
                            </tr>
                            <tr>
                                <td class="cell-cross">&times;</td>
                                <td>Open deck seating</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>
        </section>

        <!-- emeraldn  class -->
        <section class="customContainer">
            <div class="emerald-class-wrapper customContainer">

                <div class="class-intro-block">
                    <h2 class="class-main-heading">Emerald Class</h2>
                    <p class="class-description-para">
                        Come sail with us in comfort on Bintan Resort Ferries' Emerald Class — the latest signature
                        effort to enhance passengers' travel experience between Singapore and Bintan Resorts.
                    </p>
                    <p class="class-description-para">
                        Feel the sea breeze with outdoor seating exclusively for Emerald Class passengers or enjoy the
                        spaciousness of your assigned seat in the Emerald Class section. Relax in the soft, comfortable
                        seats with complimentary Wi-Fi and refreshments served by our friendly service attendants.
                    </p>
                </div>

                <hr class="section-divider-line">

                <div class="privileges-block">
                    <h2 class="privileges-main-title">Suite Of Privileges</h2>

                    <div class="privilege-sub-group">
                        <h3 class="privilege-sub-heading">Check in Services from Singapore to Bintan</h3>
                        <ul class="privilege-bullet-list">
                            <li>Dedicated & Combined check-in for boarding and baggage</li>
                            <li>Seat Assignment</li>
                            <li>Use of Emerald Lounge</li>
                            <li>Priority Baggage Handling on Arrival</li>
                        </ul>
                    </div>

                    <div class="privilege-sub-group">
                        <h3 class="privilege-sub-heading">Check in Services from Bintan to Singapore</h3>
                        <ul class="privilege-bullet-list">
                            <li>Use of Bintan Executive Lounge</li>
                            <li>Expedited Immigration Clearance</li>
                            <li>Seat Assignment</li>
                            <li>Priority Baggage Handling on Arrival</li>
                        </ul>
                    </div>

                    <div class="privilege-sub-group">
                        <h3 class="privilege-sub-heading">Onboard Services</h3>
                        <ul class="privilege-bullet-list">
                            <li>Priority Boarding & Disembarkation</li>
                            <li>Complimentary Wi-Fi Onboard</li>
                            <li>Complimentary Hot & Cold Beverages</li>
                            <li>Open deck seating</li>
                        </ul>
                    </div>
                </div>

                <div class="privileges-action-row">
                    <a href="#" class="emerald-book-btn">Book Now</a>
                </div>

            </div>
        </section>

        <!-- Economy Class -->

        <section class="customContainer">
            <div class="economy-gallery-wrapper">

                <div class="economy-info-header">
                    <h2 class="economy-main-title">Economy Class</h2>
                    <p class="economy-description-text">
                        The economy class seating on Bintan Resorts Ferries offers functional seating with generous
                        legroom, ensuring a comfortable journey to the beautiful Island in Indonesia. The cushioned
                        seats in the ferry's economy class cabin provide passengers with a cozy and relaxed seating
                        experience, allowing them to enjoy the scenic voyage. The economy class seats are situated in
                        the main deck of the ferry offering wide, clear windows that provide picturesque views of the
                        journey to Bintan Resorts.
                    </p>
                    <p class="economy-description-text">
                        Passengers can expect a delightful journey with onboard amenities such as restrooms, baggage
                        compartments. Additionally, light snacks and drinks are available for purchase adding
                        convenience to the travel experience. Economy class ferry services guarantee accessibility to
                        Bintan Resorts for all travellers, presenting an optimal option for safe, dependable and
                        comfortable travel.
                    </p>
                </div>

                <div class="economy-slider-viewport-container">

                    <div class="swiper economyGallerySwiper">
                        <div class="swiper-wrapper">

                            <div class="swiper-slide">
                                <div class="gallery-media-frame">
                                    <img src="assets/images/e1.png"
                                        alt="Bintan Resorts Ferry docked side perspective profile view" />
                                </div>
                            </div>

                            <div class="swiper-slide">
                                <div class="gallery-media-frame">
                                    <img src="assets/images/e2.png"
                                        alt="White high-speed catamaran passenger vessel cruising on the open ocean" />
                                </div>
                            </div>

                            <div class="swiper-slide">
                                <div class="gallery-media-frame">
                                    <img src="assets/images/e3.png"
                                        alt="Spacious inner main passenger deck seating layout rows looking toward windows" />
                                </div>
                            </div>

                            <div class="swiper-slide">
                                <div class="gallery-media-frame">
                                    <img src="assets/images/e4.png"
                                        alt="Premium padded ergonomic multi-colored seat structural layouts inside the cabin" />
                                </div>
                            </div>
                            <div class="swiper-slide">
                                <div class="gallery-media-frame">
                                    <img src="assets/images/e1.png"
                                        alt="Bintan Resorts Ferry docked side perspective profile view" />
                                </div>
                            </div>

                            <div class="swiper-slide">
                                <div class="gallery-media-frame">
                                    <img src="assets/images/e2.png"
                                        alt="White high-speed catamaran passenger vessel cruising on the open ocean" />
                                </div>
                            </div>

                            <div class="swiper-slide">
                                <div class="gallery-media-frame">
                                    <img src="assets/images/e3.png"
                                        alt="Spacious inner main passenger deck seating layout rows looking toward windows" />
                                </div>
                            </div>

                            <div class="swiper-slide">
                                <div class="gallery-media-frame">
                                    <img src="assets/images/e4.png"
                                        alt="Premium padded ergonomic multi-colored seat structural layouts inside the cabin" />
                                </div>
                            </div>

                        </div>
                    </div>

                    <button class="custom-nav-btn nav-btn-prev" aria-label="Previous Slide">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="19" y1="12" x2="5" y2="12"></line>
                            <polyline points="12 19 5 12 12 5"></polyline>
                        </svg>
                    </button>
                    <button class="custom-nav-btn nav-btn-next" aria-label="Next Slide">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                            <polyline points="12 5 19 12 12 19"></polyline>
                        </svg>
                    </button>

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