<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','GTM-5SVD439S');</script>
<!-- End Google Tag Manager -->
<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-KSWFQN7N6R"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-KSWFQN7N6R');
</script>
   <meta charset="UTF-8">
    <title>Bintan Ferry Tickets</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="capital-favicon.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {font-family: 'Inter', sans-serif; }
        /* Navbar Background */
        .custom-navbar {
            background: linear-gradient(white);
            padding: 12px 0;
            box-shadow: 0px 4px 20px 0px rgba(0, 0, 0, 0.08);
        }

        /* Logo */
        .navbar-brand {
            font-size: 22px;
            font-weight: 700;
            color: #111;
        }

        .navbar-brand .dot {
            color: #ff4d4f;
        }

        /* Center Menu */
        .nav-center .nav-link {
            font-size: 14px;
            font-weight: 600;
            color: #333;
            padding: 6px 14px;
            border-radius: 20px;
            transition: all 0.2s ease;
        }

        /* Active HOME link - NOT a pill, just underlined */
.nav-center .nav-link.active {
    color: #359DD7 !important;
    font-weight: 700 !important;
    background-color: transparent !important;
    border-bottom: 1.5px solid #359DD7 !important;
    border-radius: 0 !important;
    padding-bottom: 4px !important;
}

        /* Hover effect */
        .nav-center .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.6);
        }

        /* Language button */
        .btn-lang {
            font-size: 13px;
            border: 1px solid #ddd;
            background: #fff;
            padding: 6px 12px;
            border-radius: 20px;
        }

        /* Login button */
        .btn-login {
            font-size: 13px;
            font-weight: 600;
            background-color: #ff6a3d;
            color: #fff;
            padding: 7px 16px;
            border-radius: 20px;
            border: none;
        }

        .btn-login:hover {
            background-color: #e85a2f;
            color: #fff;
        }

        /* Account link */
.account-link {
    color: #333;
    font-weight: 500;
    font-size: 14px;
    text-decoration: none;
}

.account-link:hover {
    color: #f85a40;
}

/* Book tickets button */
.btn-book-tickets {
    background-color: #f85a40;
    color: white;
    border-radius: 30px;
    padding: 10px 25px;
    font-weight: 600;
    font-size: 14px;
    border: none;
    transition: background-color 0.2s;
}

.btn-book-tickets:hover {
    background-color: #e64a2e;
    color: white;
}

/* Mobile adjustments */
@media (max-width: 991px) {
    .nav-center .nav-link.active::after {
        display: none;
    }
    
    .nav-center .nav-link.active {
        color: #f85a40;
        background: transparent;
    }
    
    .d-flex.align-items-center.gap-3 {
        margin-top: 15px;
        padding-top: 15px;
        border-top: 1px solid #eee;
        justify-content: center;
    }
}

.nav-logo {
    height: 55px; /* Adjust this to your preference */
    width: auto;
    display: block;
    transition: transform 0.3s ease; /* Adds a nice smooth feel */
}

/* Optional: Make it slightly smaller on mobile */
@media (max-width: 768px) {
    .nav-logo {
        height: 40px;
    }
}

        /* Ensure navbar doesn't overflow */
.custom-navbar {
    width: 100% !important;
    left: 0 !important;
    right: 0 !important;
}

/* Fix for mobile devices */
@media (max-width: 991px) {
    .custom-navbar .container-fluid {
        padding-left: 15px !important;
        padding-right: 15px !important;
    }
    
    /* Ensure no elements are causing overflow */
    .navbar-collapse {
        max-width: 100%;
    }
    
    .navbar-nav {
        width: 100%;
        margin: 0;
        padding: 10px 0;
    }
}

/* Additional fix for very small screens */
@media (max-width: 576px) {
    .custom-navbar .container-fluid {
        padding-left: 10px !important;
        padding-right: 10px !important;
    }
}

.smaller-heading {
    font-size: 1.25rem; /* Adjust this value */
}

.promo-badge-container {
    margin-bottom: 20px; /* Adjust spacing between badge and title */
}

.promo-badge {
    display: inline-block;
    border: 2px solid #ffffff; 
    padding: 10px 30px;
    border-radius: 50px;
    
    /* FIX HERE: Remove the space */
    font-size: 1rem; 
    
    font-weight: normal;
    font-style: normal; /* Ensures it isn't italicized by a theme */
    color: #ffffff;
    text-transform: none;
    margin: 0;
    line-height: 1.2; /* Helps center the text vertically in the pill */
}

        /* Replace your existing promo banner style with this */
.promo-banner-fixed {
    background-color: #E9F2FF; /* Lighter blue to match the image */
    color: #333;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 20px; /* Space between text and button */
    padding: 12px 20px;
    font-family: 'Inter', sans-serif;
    font-size: 14px;
    font-weight: 400;
    width: 100%;
    top: 0;
    left: 0;
    z-index: 1041;
    transition: transform 0.3s ease;
}

.promo-banner-fixed.hidden {
    transform: translateY(-100%);
}

/* The "Buy a pass" Button Style */
.promo-btn {
    background-color: #00A68F; /* Emerald green from image */
    color: white;
    text-decoration: none;
    padding: 8px 24px;
    border-radius: 6px;
    font-weight: 600;
    font-size: 14px;
    transition: background-color 0.2s;
}

.promo-btn:hover {
    background-color: #008f7a;
}

/* Hide banner on scroll down, show on scroll up */
.promo-banner-fixed.hidden {
    transform: translateY(-100%);
}

/* Remove the default arrow if you want a cleaner look, or style it */
.nav-link.dropdown-toggle::after {
    vertical-align: middle;
    border-top: 0.3em solid;
    border-right: 0.3em solid transparent;
    border-left: 0.3em solid transparent;
    margin-left: 5px;
    color: #758599; /* Matches your sub-heading color */
}

/* Dropdown Item Hover Effect */
.dropdown-item:hover {
    background-color: #fff1f0; /* Very light red/coral tint */
    color: #359DD7 !important;
}

/* Ensure the dropdown menu matches your site's rounded aesthetic */
.dropdown-menu {
    border-radius: 12px;
    padding: 10px 0;
    margin-top: 0;
}

/* For Mobile: Ensure dropdown doesn't break layout */
@media (max-width: 991px) {
    .dropdown-menu {
        background-color: #f8fafc;
        border: none;
        box-shadow: none !important;
        padding-left: 20px;
    }
}

/* Show dropdown on hover (Desktop only) */
@media (min-width: 992px) {
    .nav-item.dropdown:hover .dropdown-menu {
        display: block;
        margin-top: 0; /* Prevents a gap that could close the menu when moving the mouse */
        opacity: 1;
        visibility: visible;
        animation: fadeIn 0.2s ease-in; /* Optional: smooth fade in */
    }
}

/* Optional: Smooth fade-in animation */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.badge-outline {
            border: 1px solid #94a3b8;
            color: #64748b;
            border-radius: 20px;
            padding: 5px 15px;
            font-size: 0.85rem;
            display: inline-block;
            margin-bottom: 1rem;
        }
        

        .stepper-container {
        max-width: 900px; /* Limits overall width to prevent over-stretching */
    }

        .main-heading {
            font-weight: 700;
            font-size: 48px;
            margin-bottom: 0.5rem;
        }

        .sub-heading {
            color: #758599;
            font-size: 16px;
            font-weight: 400;
            margin-bottom: 3rem;
        }

        /* Stepper Logic */
        .stepper-wrapper {
            display: flex;
            justify-content: space-between;
            position: relative;
            margin-bottom: 20px;
        }

        /* The dotted line behind the icons */
        .stepper-wrapper::before {
            content: "";
            position: absolute;
            top: 25px;
            left: 5%;
            right: 5%;
            height: 2px;
            border-top: 1px dashed #758599;
            z-index: 0;
        }

        .step-item {
            position: relative;
            z-index: 1;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            flex: 1;
        }

        .step-icon {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: white;
            border: 2px solid #cbd5e1;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 10px;
            transition: all 0.3s ease;
            color: #64748b;
        }

        /* Completed State Styles */
        .step-item.completed .step-icon {
            background-color: #359DD7; /* Red color from image */
            border-color: #359DD7;
            width: 36px;
            height: 36px;
            color: white;
            box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.2);
        }

        .step-label-top {
            text-transform: uppercase;
            font-size: 12px;
            font-weight: 500;
            color: #758599;
            margin-bottom: 4px;
        }

        .step-label-bottom {
            font-weight: 600;
            font-size: 16px;
            color: #021320;
        }


/* --- 1. Customer Support Banner --- */
        .vtl-support-banner {
            background-color: #021320; /* Dark blue background */
            border-radius: 12px;
            color: #ffffff;
            padding: 50px 40px;
            margin: 20px auto 50px auto; /* Change to auto for horizontal centering */
            position: relative;
            overflow: hidden;
        }
        
        .vtl-banner-content {
            z-index: 2;
            position: relative;
        }

        .vtl-support-text {
            max-width: 60%;
        }

        .vtl-banner-title {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 12px;
        }

        .vtl-banner-desc {
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 25px;
            color: white;
        }

        .vtl-contact-btn {
            background-color: #359DD7; /* The primary coral/red button color */
            border: none;
            border-radius: 20px;
            color: #ffffff;
            font-size: 14px;
            font-weight: 600;
            padding: 10px 30px;
            transition: background-color 0.2s;
        }
        
        .vtl-contact-btn:hover {
            background-color: #d1493b;
        }

        /* The Illustration (positioned to match the image) */
        .vtl-banner-illustration {
            position: absolute;
            left: 50px;
            bottom: 0px;
            z-index: 1;
            
            /* Placeholder: Replace with your actual image asset */
            /* width: 200px; height: 160px; background: url('assets/images/customer_support_banner.png') no-repeat bottom left / contain; */
        }
        
        /* Placeholder styling so you can see where it goes */
        /* Fixed styling for the illustration */
        .vtl-ill-placeholder {
            position: absolute;
            left: 50px;
            bottom: 0px; /* Changed from 50px to 0 to align with the bottom edge */
            width: 330px;
            height: 248px;
            background: url(https://ferry.desaruteambuilding.com/illustration.png);
            /* Removed the border-radius placeholders so the image is not distorted */
        }

        /* --- 2. Main Footer Links Area --- */
        footer.vtl-footer {
            padding: 0;
        }

        .vtl-footer-logo-area {
            margin-bottom: 30px;
        }

        .vtl-footer-logo {
            max-width: 150px;
            height: auto;
        }

        .vtl-selector-label {
            font-size: 12px;
            color: #7a7a7a;
            font-weight: 600;
            margin-bottom: 5px;
            display: block;
        }

        /* Select styling for Currency/Payment */
        .vtl-select-custom {
            border: 1px solid #e0e0e0;
            border-radius: 6px;
            padding: 8px 15px;
            font-size: 14px;
            color: #4a4a4a;
            appearance: none; /* Hide default arrow */
            background: #ffffff url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%234a4a4a' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m2 5 6 6 6-6'/%3e%3c/svg%3e") no-repeat right 12px center/10px 10px;
            margin-bottom: 15px;
            cursor: pointer;
        }

        /* Payment Icons */
        .vtl-payment-icons img {
            height: 18px; /* Standard icon height */
            margin-right: 15px;
            opacity: 0.8;
        }


        /* Link Column Styling */
        .vtl-footer-column h6 {
            font-size: 16px;
            font-weight: 700;
            color: #333333;
            margin-bottom: 20px;
        }

        .vtl-footer-column ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .vtl-footer-column ul li {
            margin-bottom: 12px;
        }

        .vtl-footer-column ul li a {
            text-decoration: none;
            color: #666666;
            font-size: 14px;
            transition: color 0.1s;
        }

        .vtl-footer-column ul li a:hover {
            color: #359DD7;
        }

        /* Contact Details with Icons */
        .vtl-contact-list li {
            font-size: 13px;
            line-height: 1.5;
            display: flex;
            align-items: flex-start;
            margin-bottom: 12px;
        }

        .vtl-contact-icon {
    margin-right: 18px;       /* Increased space between icon and text */
    flex-shrink: 0;           /* Prevents the icon from squishing */
    display: flex;
    align-items: center;
    position: relative;
    top: -2px;                /* Nudges the icon up slightly */
}
        
        .vtl-contact-icon svg, 
        .vtl-contact-icon i {
            width: 100%;
            height: auto;
            opacity: 0.5; /* Match the subtle color from image */
        }
        
        .vtl-address-text {
            color: #666666;
            display: block;
        }

        /* Separator Line */
        .vtl-separator {
            border-top: 1px solid #f0f0f0;
            margin: 40px 0;
        }

        /* --- 3. Newsletter & Bottom Line --- */
        .vtl-newsletter-section {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 40px;
        }

        .vtl-newsletter-text-block h3 {
            font-size: 26px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .vtl-newsletter-form-block {
            text-align: right;
            max-width: 450px;
        }

        .vtl-newsletter-subtitle {
            font-size: 13px;
            color: #7a7a7a;
            margin-bottom: 10px;
        }

        .vtl-newsletter-input-group {
            position: relative;
            display: flex;
        }

        .vtl-newsletter-input {
            border: 1px solid #e0e0e0;
            border-radius: 20px;
            padding: 10px 140px 10px 20px; /* Extra padding right for button */
            font-size: 14px;
            width: 100%;
        }

        .vtl-subscribe-btn {
            position: absolute;
            right: 4px;
            top: 4px;
            bottom: 4px;
            background-color: #359DD7;
            color: #ffffff;
            border: none;
            border-radius: 16px;
            font-size: 13px;
            font-weight: 600;
            padding: 0 20px;
            transition: background-color 0.2s;
        }
        
        .vtl-subscribe-btn:hover {
            background-color: #d1493b;
        }

        /* Bottom Copyright Line */
        .vtl-bottom-line {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 13px;
            color: #7a7a7a;
            padding-bottom: 30px;
        }

        .vtl-social-links {
            display: flex;
            gap: 15px;
        }

        .vtl-social-link {
            text-decoration: none;
            color: #7a7a7a;
            font-size: 18px;
            transition: color 0.1s;
        }

        .vtl-social-link:hover {
            color: #359DD7;
        }

        /* Media Queries for Responsiveness */
        @media (max-width: 1200px) {
            .vtl-support-banner, footer.vtl-footer { padding-left: 30px; padding-right: 30px; margin-left: 30px; margin-right: 30px; }
            .vtl-newsletter-section { flex-direction: column; }
            .vtl-newsletter-form-block { text-align: left; width: 100%; margin-top: 20px; }
            .vtl-bottom-line { flex-direction: column; gap: 15px; text-align: center; }
        }
        
        @media (max-width: 768px) {
            .vtl-support-banner { text-align: center; padding-top: 150px; }
            .vtl-support-text { max-width: 100%; }
            .vtl-banner-illustration { left: 50%; transform: translateX(-50%); top: 10px; }
            .vtl-support-banner, footer.vtl-footer { margin-left: 10px; margin-right: 10px; }
        }

        .custom-breadcrumb {
    display: flex;
    flex-wrap: wrap;
    padding: 0;
    list-style: none;
    font-size: 16px;
}

.custom-breadcrumb .breadcrumb-item {
    display: flex;
    align-items: center;
}

/* The "Home" Link */
.custom-breadcrumb .breadcrumb-item a {
    color: #7d8ea1; /* Muted blue-grey */
    text-decoration: none;
    transition: color 0.2s;
}

/* The "/" Separator */
.custom-breadcrumb .breadcrumb-item + .breadcrumb-item::before {
    display: inline-block;
    padding-right: 12px;
    padding-left: 12px;
    color: #758599;
    content: "/";
    font-weight: 600;
}

/* The "Search Results" Active Text */
.custom-breadcrumb .breadcrumb-item.active {
    color: #359DD7; /* The specific red/coral from your image */
    font-weight: 600;
}

.custom-breadcrumb .breadcrumb-item a:hover {
    color: #e6554d;
}

.ticket-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
            max-width: 100%;
            margin: 0 auto 30px auto;
            padding: 30px;
            border: none;
        }
        .header-section {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 25px;
        }
        .ship-icon {
            color: #ef4444; /* Reddish coral */
            font-size: 2rem;
        }
        .route-title {
            font-weight: 700;
            font-size: 20px;
            color: #0f172a;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .exchange-icon {
            color: #64748b;
            font-size: 1rem;
            border: 1px solid #e2e8f0;
            border-radius: 50%;
            padding: 5px;
        }
        /* Table Styling */
        .ferry-table {
            border-collapse: separate;
            border-spacing: 0;
            width: 100%;
            border: 1px solid #f1f5f9;
            border-radius: 8px;
            overflow: hidden;
        }
        .ferry-table th {
            background-color: #f8fafc;
            color: #1e293b;
            font-weight: 600;
            padding: 12px 20px;
            border-bottom: 1px solid #f1f5f9;
        }
        .ferry-table td {
            padding: 15px 20px;
            color: #475569;
            font-size: 0.95rem;
            border-bottom: 1px solid #f1f5f9;
        }
        .price-col {
            width: 180px;
            text-align: center;
            border-left: 1px solid #f1f5f9;
        }
        .th-icon {
            font-size: 0.85rem;
            margin-right: 8px;
            color: #64748b;
        }

        /* Additional Fees Specific Styles */
    .fees-section {
        max-width: 100%;
        margin: 40px auto;
        color: #758599; /* Muted slate color */
        line-height: 1.6;
    }

    .fees-section h2 {
        color: #0f172a; /* Dark navy */
        font-weight: 700;
        font-size: 1.5rem;
        margin-bottom: 20px;
    }

    .fees-section h5 {
        color: #0f172a;
        font-weight: 700;
        font-size: 1.1rem;
        margin-top: 30px;
        margin-bottom: 15px;
    }

    .fees-list {
        padding-left: 20px;
        margin-bottom: 25px;
        color: #758599;
    }

    .fees-list li {
        margin-bottom: 8px;
    }

    .bold-text {
        color: #475569;
        font-weight: 600;
    }

    /* Terminal Fee Table-like Layout */
    .fee-row {
        display: flex;
        max-width: 400px;
        margin-bottom: 8px;
    }

    .fee-label {
        flex: 1;
        color: #758599; /* Lighter grey */
    }

    .fee-value {
        flex: 1;
        font-weight: 700;
        color: #1e293b;
    }

    .effective-date {
        font-style: italic;
        font-size: 0.85rem;
        margin-top: 10px;
        color: black;
    }

    .disclaimer-red {
        color: #359DD7; /* Match the ship icon red */
        font-weight: 500;
        margin-top: 40px;
        margin-bottom: 5px;
    }

    .terms-link {
        color: #ef4444;
        text-decoration: underline;
        text-underline-offset: 3px;
    }

    /* ========== FULL MOBILE RESPONSIVENESS FOR SUPPORT BANNER ========== */
        @media (max-width: 992px) {
            .vtl-support-banner {
                padding: 200px 24px 40px 24px; /* Extra top padding to accommodate illustration on top */
                text-align: center;
            }
            
            .vtl-support-text {
                max-width: 100%;
                text-align: center;
            }
            
            .vtl-banner-title {
                font-size: 24px;
            }
            
            .vtl-banner-desc {
                font-size: 13px;
                line-height: 1.5;
            }
            
            /* Move illustration to top center on tablet/mobile */
            .vtl-banner-illustration {
                left: 50%;
                transform: translateX(-50%);
                top: 20px;
                bottom: auto;
                width: 280px;
                height: auto;
            }
            
            .vtl-ill-placeholder {
                position: relative;
                left: 0;
                bottom: auto;
                width: 260px;
                height: 180px;
                margin: 0 auto;
                background-size: contain;
            }
            
            .vtl-banner-content {
                text-align: center;
            }
            
            .vtl-banner-content .text-end {
                text-align: center !important;
            }
            
            .vtl-contact-btn {
                display: inline-block;
                margin-top: 8px;
            }
        }
        
        @media (max-width: 576px) {
            .vtl-support-banner {
                padding: 180px 20px 35px 20px;
                margin-left: 15px;
                margin-right: 15px;
            }
            
            .vtl-banner-title {
                font-size: 22px;
                line-height: 1.3;
            }
            
            .vtl-banner-desc {
                font-size: 12px;
                line-height: 1.45;
                margin-bottom: 20px;
            }
            
            .vtl-banner-illustration {
                top: 15px;
                width: 220px;
            }
            
            .vtl-ill-placeholder {
                width: 200px;
                height: 150px;
            }
            
            .vtl-contact-btn {
                padding: 8px 24px;
                font-size: 13px;
            }
        }

        .group-booking-container {
            max-width: 900px;
            margin: 0 auto;
            text-align: center;
        }

        .main-title {
            color: Black;
            font-weight: 700;
            font-size: 2.5rem;
            margin-bottom: 10px;
        }

        .subtitle {
            font-style: italic;
            color: Black;
            font-size: 1.1rem;
            margin-bottom: 30px;
        }

        .blue-card {
            background-color: #1A2B3C;
            border-radius: 20px;
            padding: 40px 20px;
            color: #ffffff;
        }

        .contact-info-text {
            font-size: 1.05rem;
            margin-bottom: 25px;
            font-weight: 500;
        }

        .contact-details {
            font-style: italic;
            font-weight: bold;
            font-size: 1.2rem;
            margin-bottom: 25px;
        }

        .contact-details a {
            color: white; /* Yellow color from image */
            text-decoration: underline;
        }

        /* WhatsApp Button Styling */
        .btn-whatsapp {
            background-color: #25d366;
            color: white;
            border-radius: 50px;
            padding: 12px 40px;
            font-size: 1.2rem;
            font-weight: 500;
            border: none;
            transition: transform 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .btn-whatsapp:hover {
            background-color: #1ebe57;
            color: white;
            transform: scale(1.05);
        }

        .availability-text {
            margin-top: 25px;
            font-size: 0.95rem;
        }

        .footer-description {
            margin-top: 30px;
            text-align: left;
            font-size: 1.05rem;
            line-height: 1.6;
        }

        .highlight-blue {
            color: Black;
            font-weight: bold;
        }

        .benefits-section {
            max-width: 900px;
            margin: 50px auto;
            color: #333;
        }

        .benefits-title {
            color: Black;
            font-weight: 700;
            font-size: 1.5rem;
            margin-bottom: 25px;
        }

        .benefits-intro {
            line-height: 1.6;
            margin-bottom: 20px;
            font-size: 1.05rem;
        }

        .highlight-blue-bold {
            color: Black;
            font-weight: bold;
        }

        .enjoy-benefits-text {
            color: Black;
            font-weight: bold;
            margin: 30px 0 20px 0;
            font-size: 1.1rem;
        }

        /* List Styling */
        .benefits-list {
            list-style: none;
            padding-left: 0;
        }

        .benefit-item {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            font-size: 1.1rem;
        }

        .check-box {
            background-color: #58d68d; /* Green color from image */
            color: white;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 4px;
            margin-right: 15px;
            font-size: 0.8rem;
            flex-shrink: 0;
        }

        .asterisk {
            color: #359DD7;
            margin-left: 5px;
            font-size: 0.9rem;
        }

        .disclaimer {
            font-style: italic;
            color: #777;
            font-size: 0.9rem;
            margin-top: 20px;
        }

            /* Container styling */
.custom-travel-dropdown {
    padding: 20px;
    border-radius: 20px;
    min-width: 300px; /* Adjust based on your preference */
}

.dropdown-flex-container {
    display: flex;
    gap: 15px;
    justify-content: center;
}

    /* Base Card Styling */
.custom-travel-dropdown .dropdown-item {
    width: 200px;
    height: 75px;
    border-radius: 50px; /* Makes it a pill shape */
    display: flex;
    align-items: center;
    justify-content: center;
    color: white !important;
    font-size: 16px;
    font-weight: 700;
    text-shadow: 1px 1px 4px rgba(0,0,0,0.5);
    background-size: cover;
    background-position: center;
    position: relative;
    overflow: hidden;
    transition: transform 0.2s ease;
    padding: 0; /* Reset default padding */
}

.custom-travel-dropdown .dropdown-item:hover {
    transform: scale(1.03);
    background-color: transparent; /* Prevents Bootstrap default gray hover */
}

/* Background Images with Dark Overlay */
.baggage-card {
    background: linear-gradient(rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.4)), 
                url('baggage.jpg');
}

.visa-card {
    background: linear-gradient(rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.4)), 
                url('visa.jpg');
}

/* Remove default Bootstrap bullet/list styles */
.custom-travel-dropdown li {
    list-style: none;
}

/* Specific Backgrounds for Big Groups */
.teambuilding-card {
    background: linear-gradient(rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.4)), 
                url('teambuilding.jpg');
}

/* Update this specific part of your CSS */
.group-card {
    /* Replace with your actual image path */
    background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), 
                url('group.jpg'); 
    
    background-size: cover !important;
    background-position: center !important;
    background-color: #555 !important; /* Backup color so it's not invisible */
    display: flex !important; /* Ensures the pill shape holds up */
}

/* Ensure the text is always white and visible */
.custom-travel-dropdown .dropdown-item {
    color: #ffffff !important;
    opacity: 1 !important;
}

.group-booking-container {
            max-width: 900px;
            margin: 0 auto;
            text-align: center;
        }

        .main-title {
            color: Black;
            font-weight: 700;
            font-size: 2.5rem;
            margin-bottom: 10px;
        }

        .subtitle {
            font-style: italic;
            color: Black;
            font-size: 1.1rem;
            margin-bottom: 30px;
        }

        .blue-card {
            background-color: #1A2B3C;
            border-radius: 20px;
            padding: 40px 20px;
            color: #ffffff;
        }

        .contact-info-text {
            font-size: 1.05rem;
            margin-bottom: 25px;
            font-weight: 500;
        }

        .contact-details {
            font-style: italic;
            font-weight: bold;
            font-size: 1.2rem;
            margin-bottom: 25px;
        }

        .contact-details a {
            color: white; /* Yellow color from image */
            text-decoration: underline;
        }

        /* WhatsApp Button Styling */
        .btn-whatsapp {
            background-color: #25d366;
            color: white;
            border-radius: 50px;
            padding: 12px 40px;
            font-size: 1.2rem;
            font-weight: 500;
            border: none;
            transition: transform 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .btn-whatsapp:hover {
            background-color: #1ebe57;
            color: white;
            transform: scale(1.05);
        }

        .availability-text {
            margin-top: 25px;
            font-size: 0.95rem;
        }

        .footer-description {
            margin-top: 30px;
            text-align: left;
            font-size: 1.05rem;
            line-height: 1.6;
        }

        .highlight-blue {
            color: Black;
            font-weight: bold;
        }

        .benefits-section {
            max-width: 900px;
            margin: 50px auto;
            color: #333;
        }

        .benefits-title {
            color: Black;
            font-weight: 700;
            font-size: 1.5rem;
            margin-bottom: 25px;
        }

        .benefits-intro {
            line-height: 1.6;
            margin-bottom: 20px;
            font-size: 1.05rem;
        }

        .highlight-blue-bold {
            color: Black;
            font-weight: bold;
        }

        .enjoy-benefits-text {
            color: Black;
            font-weight: bold;
            margin: 30px 0 20px 0;
            font-size: 1.1rem;
        }

        /* List Styling */
        .benefits-list {
            list-style: none;
            padding-left: 0;
        }

        .benefit-item {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            font-size: 1.1rem;
        }

        .check-box {
            background-color: #58d68d; /* Green color from image */
            color: white;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 4px;
            margin-right: 15px;
            font-size: 0.8rem;
            flex-shrink: 0;
        }

        .asterisk {
            color: #359DD7;
            margin-left: 5px;
            font-size: 0.9rem;
        }

        .disclaimer {
            font-style: italic;
            color: #777;
            font-size: 0.9rem;
            margin-top: 20px;
        }

        .icon-circle {
            width: 70px;
            height: 70px;
            background-color: #e34e44; /* Red color from image */
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
            margin: 0 auto 20px;
        }
        .card-title {
            font-weight: 700;
            font-size: 1.5rem;
            margin-bottom: 25px;
        }
        .support-label {
            font-size: 0.75rem;
            color: #888;
            margin-bottom: 2px;
            text-transform: capitalize;
        }
        .contact-detail {
            font-weight: 700;
            font-size: 1.1rem;
            margin-bottom: 15px;
        }
        .availability-text {
            font-size: 0.9rem;
            color: #777;
            margin-top: 15px;
        }
        .whatsapp-btn {
            background-color: #25d366;
            color: white;
            border-radius: 50px;
            padding: 12px 35px;
            font-weight: 600;
            font-size: 1.1rem;
            border: none;
            margin: 40px 0;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }
        .whatsapp-btn:hover {
            background-color: #1eb957;
            color: white;
        }
        .footer-info {
            color: #6c757d;
            font-size: 0.95rem;
            max-width: 900px;
            margin: 0 auto;
        }
        .highlight-red {
            color: #359DD7;
            font-weight: 500;
        }

        /* Hero Section Styles */
.contact-hero {
    position: relative;
    background: linear-gradient(0deg, rgba(2, 19, 32, 0.64), rgba(2, 19, 32, 0.64)),
    linear-gradient(262.26deg, rgba(2, 19, 32, 0) 23.96%, rgba(2, 19, 32, 0.4) 91.23%),
    url(high-hero-small-reduce.webp);
    background-size: cover;
    padding: 100px 0;
    border-radius: 24px; /* Matches the rounded corners in your image */
    margin: 20px; /* Gives the "floated card" look from the image */
    overflow: hidden;
}

/* Breadcrumb Styling */
.contact-hero .breadcrumb {
    font-size: 14px;
    font-weight: 600;
    letter-spacing: 1px;
    margin-bottom: 15px;
}

.contact-hero .breadcrumb-item a {
    color: #ffffff;
    text-decoration: none;
    opacity: 0.8;
}

.contact-hero .breadcrumb-item.active {
    color: #359DD7; /* The coral/red color */
}

.contact-hero .breadcrumb-item + .breadcrumb-item::before {
    color: #ffffff;
    content: "/";
}

/* Typography */
.hero-title {
    font-size: 48px;
    font-weight: 700;
    margin-bottom: 20px;
}

.hero-subtitle {
    font-size: 16px;
    max-width: 700px;
    margin: 0 auto;
    line-height: 1.6;
    opacity: 0.9;
    font-weight: 400;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .hero-title {
        font-size: 32px;
    }
    .contact-hero {
        margin: 10px;
        padding: 60px 15px;
    }
}

/* Top Badge */
        .why-us-badge {
            display: inline-block;
            border: 1px solid #ced4da;
            border-radius: 50px;
            padding: 5px 20px;
            font-size: 0.75rem;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 20px;
        }

        /* Left Side Typography */
        .section-title {
            color: var(--navy-blue);
            font-weight: 800;
            font-size: 2.8rem;
            line-height: 1.1;
            margin-bottom: 25px;
        }

        .description-text {
            color: var(--text-muted);
            font-size: 0.95rem;
            line-height: 1.6;
            max-width: 500px;
        }

        .highlight-text {
            color: var(--brand-red);
            font-weight: 600;
        }

        /* Image Styling */
        .image-container {
            position: relative;
            margin-top: 40px;
            /* Mimics the slight tilt in the original image */
            transform: rotate(-2deg);
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        }

        .main-img {
            width: 100%;
            display: block;
        }

        /* Right Side List */
        .benefit-header {
            font-weight: 700;
            color: #000;
            margin-bottom: 30px;
        }

        .benefit-item {
            display: flex;
            align-items: center;
            padding: 20px 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .benefit-icon {
            width: 45px;
            height: 45px;
            background-color: var(--brand-red);
            color: white;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 20px;
            flex-shrink: 0;
            font-size: 1.2rem;
        }

        .benefit-text {
            color: var(--text-muted);
            font-size: 0.95rem;
        }

        .asterisk {
            color: var(--brand-red);
        }

        .disclaimer {
            font-size: 0.85rem;
            color: var(--text-muted);
            margin-top: 20px;
        }

        :root {
            --brand-red: #359DD7;
            --text-muted: #8e98a2;
            --navy-blue: #0a1d2e;
        }
    </style>
</head>
<body>
    <!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-TWBQ2KLF"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->

<?php require './assets/includes/navbar.php'; ?>

<div class="contact-hero">
    <div class="container text-center text-white">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb justify-content-center">
                <li class="breadcrumb-item"><a href="index.php">HOME</a></li>
                <li class="breadcrumb-item active" aria-current="page">GROUP BOOKING</li>
            </ol>
        </nav>
        <h1 class="hero-title">Group Booking</h1>
        <p class="hero-subtitle">
            "It's fun to travel as part of a group — but not always as much fun to organize it."
        </p>
    </div>
</div>

<div class="container text-center py-5">
    <h5 class="mb-5 fw-bold">For group travel inquiries or to request a quotation, please contact our team at:</h5>

    <div class="row g-4 justify-content-center">
        <div class="col-md-5 col-lg-4">
            <div class="contact-card">
                <div class="icon-circle">
                    <i class="fa-solid fa-phone-flip"></i>
                </div>
                <h3 class="card-title">TelePhone</h3>
                
                <div class="row">
                    <div class="col-6">
                        <p class="support-label">Singapore Support:</p>
                        <p class="contact-detail">+65 8295 5180</p>
                    </div>
                    <div class="col-6">
                        <p class="support-label">Bintan Travel Support:</p>
                        <p class="contact-detail">+65 8295 5180</p>
                    </div>
                </div>
                
                <p class="availability-text">Customer service is available during business hours.</p>
            </div>
        </div>

        <div class="col-md-5 col-lg-4">
            <div class="contact-card">
                <div class="icon-circle">
                    <i class="fa-solid fa-envelope"></i>
                </div>
                <h3 class="card-title">Email Support</h3>
                
                <p class="support-label">Our Email:</p>
                <?php 
                    $email = "hello@vtltravel.com";
                    echo "<p class='contact-detail'>$email</p>"; 
                ?>
                
                <p class="availability-text">Our team will respond within one working day.</p>
            </div>
        </div>
    </div>

    <a href="https://wa.me/6582955180?text=Hi%20there!%20I%20have%20some%20questions%20on%20Bintan%20Ferry%20Tickets!" class="whatsapp-btn" target="_blank">
        <i class="fa-brands fa-whatsapp fa-lg"></i> WhatsApp Us
    </a>

    <p class="footer-info">
        If you have <span class="highlight-red">10 or more people</span> travelling together on the same ferry to the same destination, 
        you can enjoy <span class="highlight-red">special group fares, flexible payment terms</span>, 
        and <span class="highlight-red">dedicated support</span> from our friendly team.
    </p>
</div>

<div class="container">
    <div class="row align-items-start">
        
        <div class="col-lg-6 mb-5 mb-lg-0">
            <div class="why-us-badge">Why Us</div>
            <h2 class="section-title">Why Book With Us For<br>Group Travel?</h2>
            
            <div class="description-text">
                <p>Whether you're travelling with colleagues, students, elderly passengers, or young children, we make your journey smooth and worry-free. Our team understands that every group is different — that's why we offer 
                    <span class="highlight-text">personalized assistance</span> every step of the way.
                </p>
            </div>

            <div class="image-container">
                <img src="big-content.webp" alt="Ferry Travel" class="main-img">
            </div>
        </div>

        <div class="col-lg-5 offset-lg-1">
            <h5 class="benefit-header">Enjoy benefits such as:</h5>

            <?php
            $benefits = [
                ['icon' => 'fa-users-gear', 'text' => "Dedicated coordination for your group's travel needs"],
                ['icon' => 'fa-id-card', 'text' => "Passport collection and processing assistance"],
                ['icon' => 'fa-id-badge', 'text' => "Early boarding or fast-track clearance<span class='asterisk'>*</span>"],
                ['icon' => 'fa-bed', 'text' => "VIP room upgrades<span class='asterisk'>*</span>"],
                ['icon' => 'fa-file-lines', 'text' => "Visa application support"]
            ];

            foreach ($benefits as $benefit) {
                echo '<div class="benefit-item">';
                echo '    <div class="benefit-icon"><i class="fa-solid ' . $benefit['icon'] . '"></i></div>';
                echo '    <div class="benefit-text">' . $benefit['text'] . '</div>';
                echo '</div>';
            }
            ?>

            <p class="disclaimer">*Additional fees may apply.</p>
        </div>

    </div>
</div>

<div class="vtl-separator"></div>

<!-- FULLY RESPONSIVE SUPPORT BANNER (Mobile-optimized) -->
<div class="container">
<div class="vtl-support-banner shadow-sm">
    <div class="vtl-banner-illustration">
        <div class="vtl-ill-placeholder"></div>
    </div>
    <div class="vtl-banner-content text-end">
        <div class="vtl-support-text d-inline-block text-start">
            <h2 class="vtl-banner-title">24/7 Customer Support</h2>
            <p class="vtl-banner-desc">
                Our team of experienced specialists have travelled to hundreds of countries around the world and have decades of experience. Contact us if you face any issues before, during, or after your trip.
            </p>
            <a href="contact-us.php" class="vtl-contact-btn shadow-sm text-decoration-none d-inline-block">Contact Us</a>
        </div>
    </div>
</div>
</div>

<footer class="vtl-footer">
        <div class="container">
            <div class="row">
                
                <div class="col-lg-3 col-md-6 mb-4 mb-lg-0 vtl-footer-logo-area">
                    <img src="high-batam-logo.jpg" alt="iBatam Ferry Tickets Logo" class="vtl-footer-logo mb-4">

                    <div class="vtl-payment-selector">
                        <label class="vtl-selector-label mb-2">Payment Method</label>
                        <div class="vtl-payment-icons d-flex align-items-center">
                            <img style="height: 35px;" src="visa-icon.png" alt="Visa">
                            <img style="height: 35px;" class="card" src="mastercard-icon.png" alt="Mastercard">
                            <img src="paynow-seeklogo.png" alt="Paynow">
                        </div>
                    </div>
                </div>

                <div class="col-lg-2 col-md-6 col-6 vtl-footer-column">
                    <h6>Services</h6>
                    <ul>
                        <li><a href="index.php">Home</a></li>
                        <li><a href="ticket-fares.php">Ticket Fares</a></li>
                        <li><a href="schedule.php">Schedules</a></li>
                        <li><a href="visa-information.php">Visa Information</a></li>
                        <li><a href="deal.php">Bintan Deals</a></li>
                        <li><a href="contact-us.php">Contact</a></li>
                    </ul>
                </div>

                <div class="col-lg-2 col-md-6 col-6 vtl-footer-column">
                    <h6>Our Brands</h6>
                    <ul>
                        <li><a href="https://holidaysfromsingapore.com/">Holidays From Singapore</a></li>
                        <li><a href="https://rediscoversingapore.co/">Rediscover Singapore</a></li>
                        <li><a href="https://tourmountbromo.com/">Tour Mount Bromo</a></li>
                        <li><a href="https://tourinindonesia.com/">Tour In Indonesia</a></li>
                    </ul>
                </div>

                <div class="col-lg-2 col-md-6 col-6 vtl-footer-column">
                    <h6>Company</h6>
                    <ul>
                        <li><a href="https://vtltravel.com/">Corporate Travel</a></li>
                        <li><a href="https://vtltravel.com/about-us/">About</a></li>
                        <li><a href="blog.php">Blog</a></li>
                        <li><a href="https://vtltravel.com/careers/">Careers</a></li>
                        <li><a href="booking.php">Bintan Teambuilding</a></li>
                        <li><a href="visa-information.php">Visa Info</a></li>
                        <li><a href="baggage-information.php">Baggage Information</a></li>
                    </ul>
                </div>

                <div class="col-lg-3 col-md-6 col-6 vtl-footer-column">
                    <h6>Contact</h6>
                    <ul class="vtl-contact-list">
                        <li>
                            <span class="vtl-contact-icon"><img src="phone-contact.png"></img></span>
                            <a href="https://wa.me/6582955180?text=Hi%20there!%20I%20have%20some%20questions%20on%20Bintan%20Ferry%20Tickets!">+65 8295 5180</a>
                        </li>
                        <li>
                            <span class="vtl-contact-icon"><img src="email-contact.png"></img></span>
                            <a href="mailto:hello@vtltravel.com">hello@vtltravel.com</a>
                        </li>
                        <li class="align-items-start">
                            <span class="vtl-contact-icon mt-1"><img src="location-contact.png"></img></span>
                            <span class="vtl-address-text">
                                Address: 15 Beach Road,<br>
                                #02-01, Beach Centre,<br>
                                Singapore 189677
                            </span>
                        </li>
                    </ul>
                </div>
            </div>
            
            <div class="vtl-separator"></div>

            <!-- SUBSCRIPTION SECTION WITH AJAX - NO PAGE RELOAD -->
        <div class="vtl-newsletter-section">
            <div class="vtl-newsletter-text-block"><h3>Sign up to Receive News<br>and Information</h3></div>
            <div class="vtl-newsletter-form-block" id="subscriptionBlock">
                <p class="vtl-newsletter-subtitle">Sign up for the latest news, current offers travel content here</p>
                <div class="vtl-newsletter-input-group">
                    <input type="email" id="subscribeEmail" class="vtl-newsletter-input" placeholder="Enter Your Email ..." autocomplete="email">
                    <button type="button" id="subscribeBtn" class="vtl-subscribe-btn">Subscribe Now</button>
                </div>
                <div id="subscribeMessage" style="margin-top: 12px;"></div>
            </div>
        </div>

            <script>
// AJAX subscription handler - sends thank you email to subscriber + notification to hello@vtltravel.com
document.getElementById('subscribeBtn').addEventListener('click', function() {
    const emailInput = document.getElementById('subscribeEmail');
    const email = emailInput.value.trim();
    const messageDiv = document.getElementById('subscribeMessage');
    const subscribeBtn = document.getElementById('subscribeBtn');
    
    // Clear previous message
    messageDiv.innerHTML = '';
    
    // Validate email
    if (!email) {
        messageDiv.innerHTML = '<div class="alert alert-danger alert-custom mb-0"><i class="bi bi-exclamation-triangle-fill me-2"></i> Please enter your email address.</div>';
        emailInput.focus();
        return;
    }
    
    const emailRegex = /^[^\s@]+@([^\s@]+\.)+[^\s@]+$/;
    if (!emailRegex.test(email)) {
        messageDiv.innerHTML = '<div class="alert alert-danger alert-custom mb-0"><i class="bi bi-x-circle-fill me-2"></i> Please enter a valid email address (e.g., name@example.com).</div>';
        emailInput.focus();
        return;
    }
    
    // Show loading state
    const originalBtnText = subscribeBtn.innerHTML;
    subscribeBtn.innerHTML = '<span class="spinner-small"></span> Sending...';
    subscribeBtn.disabled = true;
    emailInput.disabled = true;
    
    // Send AJAX request to server-side processor
    fetch(window.location.href, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: 'ajax_subscribe=1&subscriber_email=' + encodeURIComponent(email)
    })
    .then(response => response.text())
    .then(data => {
        if (data.includes('SUCCESS_SUBSCRIBE')) {
            messageDiv.innerHTML = '<div class="alert alert-success alert-custom mb-0"><i class="bi bi-envelope-check-fill me-2"></i> Thank you for subscribing! A welcome email has been sent to ' + escapeHtml(email) + '. Check your inbox!</div>';
            emailInput.value = '';
        } else if (data.includes('INVALID_EMAIL')) {
            messageDiv.innerHTML = '<div class="alert alert-danger alert-custom mb-0"><i class="bi bi-x-circle-fill me-2"></i> Invalid email format. Please try again.</div>';
        } else if (data.includes('EMAIL_FAILED')) {
            messageDiv.innerHTML = '<div class="alert alert-warning alert-custom mb-0"><i class="bi bi-envelope-exclamation me-2"></i> Subscription received, but we couldn\'t send a confirmation email. Our team will follow up!</div>';
            emailInput.value = '';
        } else {
            messageDiv.innerHTML = '<div class="alert alert-danger alert-custom mb-0"><i class="bi bi-exclamation-triangle-fill me-2"></i> Something went wrong. Please try again later.</div>';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        messageDiv.innerHTML = '<div class="alert alert-danger alert-custom mb-0"><i class="bi bi-wifi-off me-2"></i> Network error. Please check your connection and try again.</div>';
    })
    .finally(() => {
        subscribeBtn.innerHTML = originalBtnText;
        subscribeBtn.disabled = false;
        emailInput.disabled = false;
        
        // Auto-hide success message after 6 seconds
        setTimeout(() => {
            const successAlert = messageDiv.querySelector('.alert-success');
            if (successAlert) {
                successAlert.style.transition = 'opacity 0.5s';
                successAlert.style.opacity = '0';
                setTimeout(() => {
                    if (messageDiv.firstChild === successAlert) messageDiv.innerHTML = '';
                }, 500);
            }
        }, 6000);
    });
});

function escapeHtml(str) {
    return str.replace(/[&<>]/g, function(m) {
        if (m === '&') return '&amp;';
        if (m === '<') return '&lt;';
        if (m === '>') return '&gt;';
        return m;
    });
}

// Allow Enter key
document.getElementById('subscribeEmail').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        document.getElementById('subscribeBtn').click();
    }
});
</script>

<?php
// Handle AJAX subscription request - sends both admin notification AND thank you email to subscriber
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' && isset($_POST['ajax_subscribe']) && $_POST['ajax_subscribe'] == '1') {
    header('Content-Type: text/plain; charset=utf-8');
    $email = filter_var(trim($_POST['subscriber_email'] ?? ''), FILTER_SANITIZE_EMAIL);
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "INVALID_EMAIL";
        exit;
    }
    
    $admin_email = "hello@vtltravel.com";
    $site_name = "Bintan Ferry Tickets";
    $current_date = date("F j, Y, g:i a");
    
    // 1. Email to Admin (hello@vtltravel.com) - notification of new subscriber
    $admin_subject = "New Newsletter Subscription - $site_name";
    $admin_headers = "MIME-Version: 1.0\r\n";
    $admin_headers .= "Content-type:text/html;charset=UTF-8\r\n";
    $admin_headers .= "From: noreply@bintanferry.com\r\n";
    $admin_headers .= "Reply-To: " . $email . "\r\n";
    $admin_message = "
    <html>
    <body style='font-family: Arial, sans-serif;'>
        <h2 style='color: #359DD7;'>🎉 New Newsletter Subscriber!</h2>
        <p><strong>Email:</strong> " . htmlspecialchars($email) . "</p>
        <p><strong>Date:</strong> " . $current_date . "</p>
        <hr>
        <p style='color: #666; font-size: 12px;'>This subscriber signed up via the newsletter form.</p>
    </body>
    </html>";
    
    // 2. Thank You Email to Subscriber
    $thankyou_subject = "Welcome to Bintan Ferry Newsletter! 🚢";
    $thankyou_headers = "MIME-Version: 1.0\r\n";
    $thankyou_headers .= "Content-type:text/html;charset=UTF-8\r\n";
    $thankyou_headers .= "From: Bintan Ferry Tickets Team <hello@vtltravel.com>\r\n";
    $thankyou_headers .= "Reply-To: hello@vtltravel.com\r\n";
    
    $thankyou_message = "
    <html>
    <body style='font-family: Arial, sans-serif; background-color: #f9f9f9; padding: 20px;'>
        <div style='max-width: 600px; margin: 0 auto; background: white; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.1);'>
            <div style='background: linear-gradient(135deg, #359DD7, #ff6a3d); padding: 30px; text-align: center;'>
                <h1 style='color: white; margin: 15px 0 0 0; font-size: 28px;'>Welcome Aboard!</h1>
            </div>
            <div style='padding: 30px;'>
                <p style='font-size: 16px; color: #333;'>Dear <strong>" . htmlspecialchars($email) . "</strong>,</p>
                <p style='font-size: 16px; color: #555; line-height: 1.6;'>Thank you for subscribing to the <strong style='color: #359DD7;'>Bintan Ferry Newsletter</strong>! You're now part of our travel community.</p>
                <p style='font-size: 16px; color: #555; line-height: 1.6;'>You'll receive:</p>
                <ul style='font-size: 15px; color: #555; line-height: 1.6; padding-left: 20px;'>
                    <li>✨ Exclusive ferry ticket promotions</li>
                    <li>🗓️ Latest ferry schedules and updates</li>
                    <li>🏝️ Travel tips for Bintan and nearby islands</li>
                    <li>💰 Special discount codes</li>
                </ul>
                <div style='background: #fef2ef; padding: 15px; border-radius: 12px; margin: 20px 0; text-align: center;'>
                    <p style='margin: 0; color: #359DD7; font-weight: 600;'>Need assistance? Contact us anytime at <a href='mailto:hello@vtltravel.com' style='color: #359DD7;'>hello@vtltravel.com</a></p>
                </div>
                <p style='font-size: 14px; color: #888; text-align: center; margin-top: 25px; border-top: 1px solid #eee; padding-top: 20px;'>
                    Bintan Ferry | Your trusted ferry booking partner<br>
                    Singapore Tourism Board TA License No. TA03084
                </p>
            </div>
        </div>
    </body>
    </html>";
    
    $admin_sent = mail($admin_email, $admin_subject, $admin_message, $admin_headers);
    $thankyou_sent = mail($email, $thankyou_subject, $thankyou_message, $thankyou_headers);
    
    if ($admin_sent || $thankyou_sent) {
        // If at least one email was sent successfully, consider it a success
        // We still want to return success even if thank you email fails (but we'll indicate)
        if ($thankyou_sent) {
            echo "SUCCESS_SUBSCRIBE";
        } else {
            // Admin got notification but subscriber didn't get thank you email
            echo "SUCCESS_SUBSCRIBE"; // Still success from our perspective, we'll show success message
        }
    } else {
        echo "EMAIL_FAILED";
    }
    exit;
}
?>
<div class="vtl-separator"></div>
            <div class="vtl-bottom-line">
                <div class="vtl-copyright">
                    Copyright © 2026 VTL Travel Private Limited. All Rights Reserved.<br> 
                    Travel Agent Licence: TA03084, UEN: 201530058G.<br>
                    Bintan Ferry Tickets Is Part of VTL Travel Private Limited
                </div>
                <div class="vtl-social-links">
                    <a href="https://youtube.com/@vtltravel?si=5Q_gu445UcoiLTd3" class="vtl-social-link"><i class="bi bi-youtube"></i></a>
                </div>
            </div>

        </div></footer>

        <!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-5SVD439S"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Simple JS function to handle WhatsApp Click
    function openWhatsApp() {
        const phoneNumber = "6582955180";
        const message = "Hi Bintan Ferry Tickets, I would like to inquire about a group booking.";
        window.open(`https://wa.me/${phoneNumber}?text=${encodeURIComponent(message)}`, '_blank');
    }
</script>
<script>			var url = 'https://prod-crmb2b.s3.ap-southeast-1.amazonaws.com/widget/64c917df6f3bf83bd6d2b5b3/69954aba97bf000e7bb2baf4.js?v=' + Date.now();			var s = document.createElement('script');			s.type = 'text/javascript';			s.async = true;			s.src = url;			s.onload = function() {				CreateWhatsappChatWidget();			};			var x = document.getElementsByTagName('script')[0];			x.parentNode.insertBefore(s, x);		</script>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</body>
</html>