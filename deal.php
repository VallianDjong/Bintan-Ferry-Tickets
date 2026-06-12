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
   <meta charset="UTF-8">
     <title>Bintan Ferry Deals & Tour Packages 2026 | Bintan Ferry Tickets</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Best Bintan ferry deals and tour packages for families, groups, and corporate trips. Seafood, spa, golf, and adventure packages. Get a free quote today.">
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
    color: #359DD7;
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

        :root {
            --primary-red: #359DD7;
            --primary-dark: #2a3443;
            --text-on-dark: #ffffff;
            --card-bg: #ffffff;
            --border-color: #e2e8f0;
            --placeholder-color: #a0aec0;
            --card-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .page-wrapper {
            padding: 24px; /* Adjust this value for more/less space from the edge */
        }

        /* --- Hero Section & Video Background --- */
        .hero-section {
            position: relative;
            min-height: 80vh; /* Adjust as needed, should fill most of the viewport */
            width: 100%;
            overflow: hidden;
            display: flex;
            align-items: center; /* Vertical center */
            background: linear-gradient(rgba(13, 27, 42, 0.1), rgba(13, 27, 42, 0.1)), 
                    url('balerang-2.jpeg');
            background-size: cover;
            background-position: center;
            border-radius: 24px; /* Matches the rounded corners in your image */
        }

        #hero-video {
            position: absolute;
            top: 50%;
            left: 50%;
            min-width: 100%;
            min-height: 100%;
            width: auto;
            height: auto;
            transform: translate(-50%, -50%);
            object-fit: cover;
            z-index: 1;
        }

        /* Overlay for text legibility and coloring */
        .hero-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.25); /* Darken the video */
            background-image: linear-gradient(to right, rgba(42, 52, 67, 0.8), rgba(0, 0, 0, 0.1)); /* Add a dark/blue tint */
            z-index: 2;
        }

        .hero-content-wrapper {
            position: relative;
            z-index: 3; /* Above video and overlay */
            padding-top: 100px; /* Space from top */
            padding-bottom: 120px; /* Space to make room for overlapping stats */
        }

        .hero-text {
            max-width: 600px;
            color: var(--text-on-dark);
        }

        .hero-title {
            font-size: 56px;
            font-weight: 700;
            line-height: 1.1;
            margin-bottom: 24px;
        }

        .hero-description {
            font-size: 18px;
            font-weight: 400;
            line-height: 1.6;
            color: rgba(255, 255, 255, 0.85);
            margin-bottom: 40px;
        }

        .contact-us-btn {
            background-color: var(--primary-red);
            color: #fff;
            padding: 12px 28px;
            font-weight: 600;
            border-radius: 8px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            border: 2px solid var(--primary-red);
            transition: all 0.2s ease;
        }

        .contact-us-btn:hover {
            background-color: transparent;
            color: var(--primary-red);
        }

        /* --- Form Card --- */
        .form-card {
            background-color: var(--card-bg);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.2);
            position: relative;
            right: 0px; /* Initial positioning, adjust with bootstrap columns */
        }

        .form-card h3 {
            font-size: 24px;
            font-weight: 700;
            color: var(--primary-dark);
            margin-bottom: 30px;
        }

        .form-label {
            font-size: 14px;
            font-weight: 600;
            color: #4a5568;
            margin-bottom: 6px;
        }

        .required-star {
            color: var(--primary-red);
            font-weight: bold;
        }

        .form-control, .form-select {
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 12px 16px;
            font-size: 12px;
            color: var(--primary-dark);
        }

        .form-control::placeholder, .form-select {
            color: var(--placeholder-color);
            opacity: 1;
        }

        .form-control:focus, .form-select:focus {
            border-color: #cbd5e1;
            box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.15);
        }

        .message-area {
            height: 120px;
            resize: none;
        }

        .send-message-btn {
            background-color: var(--primary-red);
            color: #fff;
            border: none;
            padding: 14px 28px;
            border-radius: 8px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            width: fit-content;
            transition: background-color 0.2s;
        }

        .send-message-btn:hover {
            background-color: #d6413a;
        }

        /* --- Statistics Section (overlapping) --- */
        .stats-section {
            position: relative;
            top: -100px; /* Pulls the section up to overlap the hero */
            z-index: 4; /* Above the hero elements */
            margin-bottom: -100px; /* Compels the page flow to treat it as a smaller footprint */
        }

        .stat-card {
            background-color: var(--card-bg);
            border-radius: 16px;
            padding: 24px;
            text-align: center;
            box-shadow: var(--card-shadow);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
        }

        .stat-icon {
            font-size: 24px;
            color: var(--primary-dark);
            background-color: #edf2f7; /* Lighter background for the icon area */
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 16px;
        }

        .stat-value {
            font-size: 36px;
            font-weight: 700;
            color: var(--primary-dark);
            line-height: 1.2;
        }

        .stat-value-unit {
            color: var(--primary-red);
            font-weight: 700;
        }

        .stat-label {
            font-size: 12px;
            font-weight: 500;
            color: #718096;
            margin-top: 4px;
        }


        /* Responsive Adjustments */
        @media (max-width: 1200px) {
            .hero-title { font-size: 48px; }
            .form-card { padding: 30px; }
        }

        @media (max-width: 992px) {
            .hero-section { min-height: 100vh; }
            .hero-content-wrapper { padding-top: 80px; }
            .form-card { right: 0; margin-top: 50px; }
            .stats-section { top: -60px; margin-bottom: -60px;}
        }

        @media (max-width: 768px) {
            .hero-title { font-size: 38px; }
            .hero-content-wrapper { padding-bottom: 80px;}
            .stat-card { padding: 16px; }
            .stat-value { font-size: 28px; }
        }

        .badge-header {
    display: inline-block;
    border: 1px solid #ddd;
    border-radius: 20px;
    padding: 4px 15px;
    font-size: 0.7rem;
    font-weight: 600;
    color: #888;
    text-transform: uppercase;
}

/* Card Styling */
.custom-card {
    border-radius: 20px;
    overflow: hidden;
    transition: transform 0.2s;
}

.custom-card:hover {
    transform: translateY(-5px);
}

.card-img-top {
    height: 180px;
    object-fit: cover;
    padding: 10px;
    border-radius: 25px !important;
}

.price-text {
    color: #359DD7;
    font-weight: 700;
}

/* Button Toggles */
.btn-custom {
    border: none;
    padding: 8px 20px;
    font-size: 0.9rem;
    font-weight: 600;
    color: #666;
    border-radius: 8px !important;
}

.btn-custom.active {
    background-color: #359DD7;
    color: white;
    box-shadow: 0 4px 10px rgba(230, 57, 70, 0.3);
}

/* Card Buttons */
.explore-btn {
    font-size: 0.8rem;
    font-weight: 600;
    width: fit-content;
    display: flex;
    align-items: center;
    gap: 5px;
}

.explore-btn:hover {
    background-color: #359DD7;
    color: white;
    border: none;
}

.btn-danger {
    background-color: #359DD7;
    border-color: #359DD7;
}

.arrow {
    font-size: 1rem;
}

.section-badge {
            display: inline-block;
            border: 1px solid #ced4da;
            border-radius: 50px;
            padding: 5px 20px;
            font-size: 11px;
            font-weight: 600;
            color: #6c757d;
            letter-spacing: 0.5px;
            margin-bottom: 20px;
        }

        .section-title { font-weight: 800; color: #0a192f; font-size: 2.8rem; }

        /* Toggle Container */
        .toggle-group {
            background-color: #f8f9fa;
            border-radius: 12px;
            padding: 5px;
            display: inline-flex;
            gap: 5px;
            border: 1px solid #e9ecef;
        }

        .btn-toggle {
            border: none;
            padding: 10px 25px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s;
        }

        .btn-toggle.active {
            background-color: #359DD7;
            color: white;
            box-shadow: 0 4px 10px rgba(230, 57, 70, 0.2);
        }

        .btn-toggle.inactive { background-color: transparent; color: #495057; }

        /* Card Styling */
        .option-card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.04);
            transition: transform 0.3s ease;
        }

        .option-card:hover { transform: translateY(-8px); }

        .card-img-top {
            border-radius: 15px !important;
            padding: 12px;
            height: 220px;
            object-fit: cover;
        }

        .price-label { color: #359DD7; font-weight: 700; }

        .btn-explore {
            border-radius: 50px;
            padding: 8px 20px;
            font-size: 13px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        /* Styling for the text and container */
    .text-navy {
        color: #0a1d37;
        font-size: 48px;
    }

    .badge-outline {
        display: inline-block;
        padding: 5px 20px;
        border: 1px solid #ced4da;
        border-radius: 50px;
        font-size: 14px;
        color: #6c757d;
    }

    .content-text p {
        line-height: 1.7;
        font-size: 16px;
        
    }

    /* Button Styling */
    .btn-contact {
        background-color: #359DD7;
        color: white;
        border-radius: 50px;
        font-weight: 600;
        border: none;
        transition: background-color 0.3s;
    }

    .btn-contact:hover {
        background-color: #d32f2f;
        color: white;
    }

    /* The Image Rotation Logic */
    .image-wrapper {
        position: relative;
        padding: 20px;
    }

    .rotated-card {
        background: white;
        padding: 12px 12px 40px 12px; /* Thick bottom border like a polaroid */
        box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        border-radius: 8px;
        transform: rotate(-5deg); /* This creates the tilt seen in the image */
        max-width: 450px;
        transition: transform 0.3s ease;
    }

    .rotated-card:hover {
        transform: rotate(0deg) scale(1.02);
    }

    .rotated-card img {
        border-radius: 4px;
        display: block;
        width: 100%;
        height: auto;
    }

    @media (max-width: 991px) {
        .rotated-card {
            max-width: 80%;
            margin-bottom: 30px;
        }
    }

    .client-logo-card {
        background: white;
        border: 1px solid #f1f5f9;
        border-radius: 12px;
        padding: 25px 15px;
        height: 100px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
        box-shadow: 0 4px 6px rgba(0,0,0,0.02);
    }

    .client-logo-card:hover {
        box-shadow: 0 10px 20px rgba(0,0,0,0.05);
        transform: translateY(-3px);
    }

    .client-logo-card img {
        max-width: 100%;
        max-height: 70px;
        object-fit: contain;
        /* filter: grayscale(100%); Optional: makes logos more uniform */
        /* opacity: 0.7; */
    }

    .client-logo-card:hover img {
        filter: grayscale(0%);
        opacity: 1;
    }

    /* Smooth fade transition */
.animate-fade-in {
    animation: fadeIn 0.4s ease-out forwards;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(5px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Ensure active button color overrides default bootstrap */
.btn-custom.active {
    background-color: #359DD7 !important;
    color: white !important;
    border: none;
}

/* CSS for the toggle group if not already in your file */
.toggle-group {
    background-color: #f8f9fa;
    border-radius: 12px;
    padding: 5px;
    display: inline-flex;
    border: 1px solid #e9ecef;
}

.btn-toggle {
    border: none;
    padding: 8px 24px;
    border-radius: 8px;
    font-weight: 600;
    transition: all 0.3s ease;
    background: transparent;
}

.btn-toggle.active {
    background-color: #359DD7;
    color: white;
}

.btn-toggle.inactive {
    color: #6c757d;
}

/* Re-using the same fade animation for consistency */
.animate-fade-in {
    animation: fadeInOptions 0.5s ease forwards;
}

@keyframes fadeInOptions {
    from { opacity: 0; transform: translateY(15px); }
    to { opacity: 1; transform: translateY(0); }
}

.option-card {
    border-radius: 20px;
    overflow: hidden;
}

.btn-explore {
    font-size: 0.85rem;
    padding: 8px 20px;
}

/* Styling for the Services Section */
    .services-section {
        background-color: #fcfdfe; /* Very light gray/blue background */
    }

    .service-card {
        background-color: white;
        border-radius: 12px;
        border: 1px solid #f1f5f9;
        overflow: hidden;
        height: 100%;
        transition: all 0.3s ease;
        text-align: center;
        padding: 12px; /* Inner padding like the image */
        box-shadow: 0 4px 6px rgba(0,0,0,0.02);
    }

    .service-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 25px rgba(0,0,0,0.07);
    }

    .service-img-wrapper {
        width: 100%;
        height: 160px; /* Adjust height based on your preference */
        border-radius: 10px;
        overflow: hidden;
        margin-bottom: 20px;
    }

    .service-img-wrapper img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .service-content {
        padding: 0 10px 15px;
    }

    .service-title {
        font-weight: 700;
        font-size: 18px;
        color: #0d1b2a;
        margin-bottom: 12px;
    }

    .service-text {
        font-size: 13px;
        color: #94a3b8; /* Muted slate color */
        line-height: 1.6;
        margin-bottom: 0;
    }

    /* Column spacing logic for different screens */
    @media (max-width: 991px) {
        .service-title { font-size: 17px; }
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
    font-size: 12px;
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

.text-danger{
    color: #359DD7 !important;
}
    </style>
    <!-- Custom Css -->
    <link rel="stylesheet" href="./assets/css/helper.css" />
    <link rel="stylesheet" href="./assets/css/style.css" />
    <link rel="stylesheet" href="./assets/css/responsive.css" />
</head>
<body>
    <!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-TWBQ2KLF"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->

<div class="promo-banner-fixed" id="promoBanner">
    <span><strong>Enjoy</strong> 2 Way Return Bintan Ticket at <strong>$68.40/Person</strong> Only. 
    Use Promo Code - <strong>VTLTRAVEL</strong>
    <strong>*First 50 customers per day*</strong></span>
    <a href="#booking-card" class="promo-btn">Promo Code: VTLTRAVEL</a>
</div>

<?php require './assets/includes/navbar.php'; ?>

<?php
$messageStatus = "";
$messageClass = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && !isset($_POST['ajax_subscribe'])) {
    $fullName = htmlspecialchars(trim($_POST['full_name'] ?? ''));
    $email = htmlspecialchars(trim($_POST['email'] ?? ''));
    $phone = htmlspecialchars(trim($_POST['phone'] ?? ''));
    $travelDate = htmlspecialchars(trim($_POST['travel_date'] ?? ''));
    $passengers = htmlspecialchars(trim($_POST['passengers'] ?? ''));
    $route = htmlspecialchars(trim($_POST['route'] ?? ''));
    $message = htmlspecialchars(trim($_POST['message'] ?? ''));

    if (empty($fullName) || empty($email) || empty($travelDate) || empty($passengers) || empty($route)) {
        $messageStatus = "Error: Please fill in all required fields.";
        $messageClass = "alert-danger";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $messageStatus = "Error: Invalid email format.";
        $messageClass = "alert-danger";
    } else {
        $to = "hello@vtltravel.com";
        $subject = "New Ferry Booking Inquiry from $fullName";
        $emailBody = "<html><body><h3>Customer Inquiry</h3>
            <p><strong>Name:</strong> $fullName</p>
            <p><strong>Email:</strong> $email</p>
            <p><strong>Phone:</strong> $phone</p>
            <p><strong>Travel Date:</strong> $travelDate</p>
            <p><strong>Passengers:</strong> $passengers</p>
            <p><strong>Group:</strong> $route</p>
            <p><strong>Message:</strong><br>" . nl2br($message) . "</p></body></html>";
        $headers = "MIME-Version: 1.0\r\nContent-type:text/html;charset=UTF-8\r\nFrom: $email\r\nReply-To: $email";
        $mailSent = mail($to, $subject, $emailBody, $headers);
        
        if ($mailSent) {
            $messageStatus = "Thank you! Your message has been sent. We'll get back to you within 24 hours.";
            $messageClass = "alert-success";
        } else {
            $messageStatus = "Sorry, something went wrong. Please try again later or contact us directly at hello@vtltravel.com";
            $messageClass = "alert-danger";
        }
    }
}
?>

<div class="page-wrapper" style="overflow: hidden;">
    <div class="hero-section">
        <div class="hero-overlay"></div>
        <div class="container hero-content-wrapper">
            <div class="row align-items-center">
                <div class="col-lg-6 col-12 hero-text mb-5 mb-lg-0">
                    <h1 class="hero-title">Get Your Bintan Quote</h1>
                    <p class="hero-description">We offer a wide range of Bintan packages tailored for families, friends, couples, and solo travelers.</p>
                    <a href="contact-us.php" class="contact-us-btn">Contact Us &nbsp; <i class="bi bi-arrow-up-right"></i></a>
                </div>
                <div id="message-section" class="col-lg-5 col-12 ms-lg-auto">
                    <div class="form-card">
                        <h3>Send Us a Message</h3>
                        <?php if (!empty($messageStatus)): ?>
                            <div class="alert <?php echo $messageClass; ?> alert-dismissible fade show" role="alert">
                                <?php echo $messageStatus; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>#message-section" method="POST">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="full_name" class="form-label">Full Name <span class="required-star">*</span></label>
                                    <input type="text" class="form-control" id="full_name" name="full_name" placeholder="Enter full name" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email Address <span class="required-star">*</span></label>
                                    <input type="email" class="form-control" id="email" name="email" placeholder="Email address..." required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="phone" class="form-label">Phone Number</label>
                                    <input type="tel" class="form-control" id="phone" name="phone" placeholder="+65 ...">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="travel_date" class="form-label">When you going? <span class="required-star">*</span></label>
                                    <input type="date" class="form-control" id="travel_date" name="travel_date" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
    <label for="passengers" class="form-label">Number of Passengers <span class="required-star">*</span></label>
    <select class="form-select" id="passengers" name="passengers" required>
        <option value="" disabled selected>Select number</option>
        
        <option value="1">1</option>
        <option value="2">2</option>
        <option value="3">3</option>
        <option value="4">4</option>
        <option value="5">5</option>
        <option value="6">6</option>
        <option value="7">7</option>
        <option value="8">8</option>
        <option value="9">9</option>
        <option value="10">10</option>
        <option value="11">11</option>
        <option value="12">12</option>
        <option value="13">13</option>
        <option value="14">14</option>

        <option value="15-49">15+</option>
        <option value="50-99">50+</option>
        <option value="100+">100+</option>
    </select>
</div>
                                <div class="col-md-6 mb-3">
                                    <label for="route" class="form-label">What group is this?<span class="required-star">*</span></label>
                                    <select class="form-select" id="route" name="route" required>
                                        <option value="" disabled selected>Select a Group</option>
                                        <option value="Friends">Friends</option>
                                        <option value="Family">Family</option>
                                        <option value="Church">Church</option>
                                        <option value="Corporate">Corporate</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-4">
                                <label for="message" class="form-label">Let us know your requirements</label>
                                <textarea class="form-control message-area" id="message" name="message" placeholder="Tell us about your travel plans or questions."></textarea>
                            </div>
                            <button type="submit" class="send-message-btn">Send Message &nbsp; <i class="bi bi-arrow-up-right"></i></button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container stats-section">
        <div class="row row-cols-2 row-cols-md-4 g-4 justify-content-center">
            <div class="col"><div class="stat-card"><div class="stat-icon"><img src="satisfaction.png" alt="Rating"></div><div class="stat-value">4.9 <span class="stat-value-unit">&starf;</span></div><div class="stat-label">Customer Satisfaction</div></div></div>
            <div class="col"><div class="stat-card"><div class="stat-icon"><img src="booked.png" alt="Booked"></div><div class="stat-value">40K <span class="stat-value-unit">+</span></div><div class="stat-label">Tickets Booked</div></div></div>
            <div class="col"><div class="stat-card"><div class="stat-icon"><img src="daily.png" alt="Daily"></div><div class="stat-value">12 <span class="stat-value-unit">+</span></div><div class="stat-label">Daily Departures</div></div></div>
            <div class="col"><div class="stat-card"><div class="stat-icon"><img src="instant.png" alt="Instant"></div><div class="stat-value">100 <span class="stat-value-unit">%</span></div><div class="stat-label">Instant Confirmation</div></div></div>
        </div>
    </div>
    <div style="height: 100px;"></div>

        <div class="container py-5 text-center">
    <div class="badge-header mb-2">BEST FERRY DEALS</div>
    <h1 class="fw-bold mb-4">Ferry Tickets From <br> Singapore To Bintan</h1>

    <div class="d-inline-flex bg-light p-1 rounded-3 mb-5 shadow-sm">
        <button id="oneWayBtn" class="btn btn-custom active" onclick="updateContent('oneWay')">2D1N Packages</button>
        <button id="returnBtn" class="btn btn-custom" onclick="updateContent('return')">3D2N Packages</button>
    </div>

    <div class="row g-4" id="ferry-container">
        </div>
</div>

<section class="services-section py-5 bg-white">
    <div class="container">
        <div class="text-center mb-5">
            <span class="badge rounded-pill border text-muted px-3 py-2 mb-3" style="font-weight: 500; font-size: 11px; letter-spacing: 1px; text-transform: uppercase;">WHAT WE OFFER</span>
            <h2 class="fw-bold mb-4" style="color: #0d1b2a; font-size: 42px;">All Inclusive Ferry <br> Booking Services</h2>
        </div>

        <div class="row g-4">
            <div class="col-xl-3 col-lg-4 col-md-6">
                <div class="service-card">
                    <div class="service-img-wrapper">
                        <img src="seafood.webp" alt="Online Booking">
                    </div>
                    <div class="service-content">
                        <h5 class="service-title">Live Seafood</h5>
                        <p class="service-text">Bintan seafood is popular because of its freshness, affordability, unique flavors, and stunning coastal dining experiences. If you’re a seafood lover, Bintan is a must-visit destination.</p>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-lg-4 col-md-6">
                <div class="service-card">
                    <div class="service-img-wrapper">
                        <img src="massage.webp" alt="Routes">
                    </div>
                    <div class="service-content">
                        <h5 class="service-title">Massages</h5>
                        <p class="service-text">Indulge at a relaxing spa after a long day of activities. These massages are sure to melt away any stress and rejuvenate both your mind and body.</p>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-lg-4 col-md-6">
                <div class="service-card">
                    <div class="service-img-wrapper">
                        <img src="kart.webp" alt="E-Tickets">
                    </div>
                    <div class="service-content">
                        <h5 class="service-title">Go Kart</h5>
                        <p class="service-text">Enjoy a fun-filled day with Go Kart, perfect for families looking to share exciting moments together, a great addition to your Bintan Itinerary. Available Indoor and Outdoor setting.</p>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-lg-4 col-md-6">
                <div class="service-card">
                    <div class="service-img-wrapper">
                        <img src="zoo.webp" alt="Schedules">
                    </div>
                    <div class="service-content">
                        <h5 class="service-title">Paradise Mini Zoo</h5>
                        <p class="service-text">Paradise Mini Zoo Bintan is a small but charming animal park in Bintan, Indonesia. It offers visitors a chance to see and interact with various animals in a family-friendly environment.</p>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-lg-4 col-md-6">
                <div class="service-card">
                    <div class="service-img-wrapper">
                        <img src="hiking.png" alt="Operators">
                    </div>
                    <div class="service-content">
                        <h5 class="service-title">Hiking Adventure</h5>
                        <p class="service-text">Discover the natural beauty of Bintan at Panbil Nature Reserve. Surounded by greeneries and scenic trails. Perfect for nature lover and outdoor enthusiast.</p>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-lg-4 col-md-6">
                <div class="service-card">
                    <div class="service-img-wrapper">
                        <img src="water.webp" alt="Payments">
                    </div>
                    <div class="service-content">
                        <h5 class="service-title">Beach Water Sports</h5>
                        <p class="service-text">Add excitement to your holiday with thrilling beach water sports in Bintan. From Jet Skiing to group Banana Boat rides, offers the perfect adventure by the sea.</p>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-lg-4 col-md-6">
                <div class="service-card">
                    <div class="service-img-wrapper">
                        <img src="puncak.webp" alt="Group Booking">
                    </div>
                    <div class="service-content">
                        <h5 class="service-title">Puncak Beliung</h5>
                        <p class="service-text">Puncak Beliung is a hidden gem in Bintan. Offers stunning views, lush greenery, and a serene escape from the city life.</p>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-lg-4 col-md-6">
                <div class="service-card">
                    <div class="service-img-wrapper">
                        <img src="golf.webp" alt="Support">
                    </div>
                    <div class="service-content">
                        <h5 class="service-title">Golf Tour</h5>
                        <p class="service-text">Enjoy a refreshing golf getaway with our exclusive golf tour. Play at well maintained courses while soaking in beautiful scenery, perfect for leisure golfer and seasonal golfer.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


<section class="container py-5 text-center">
    <div class="section-badge">BEST FERRY TRAVEL OPTIONS</div>
    <h2 class="section-title mb-4">Bintan Ferry Travel<br>Options</h2>

    <div class="toggle-group mb-5">
        <button id="dailyBtn" class="btn-toggle active" onclick="switchTravelOptions('daily')">Day Tours</button>
        <button id="popularBtn" class="btn-toggle inactive" onclick="switchTravelOptions('popular')">Golf Packages</button>
    </div>

    <div class="row g-4 text-start" id="travel-options-container">
        </div>
</section>

<section class="booking-platform py-5">
    <div class="container py-lg-5">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-5 mb-lg-0 d-flex justify-content-center">
                <div class="image-wrapper">
                    <div class="rotated-card">
                        <img src="licence.webp" alt="Ferry" class="img-fluid">
                    </div>
                </div>
            </div>

            <div class="col-lg-6 ps-lg-5">
                <span class="badge-outline mb-3">Licensed</span>
                <h2 class="display-4 fw-bold text-navy mb-4">
                    Licensed & Trusted Ferry Booking Platform
                </h2>
                <div class="content-text text-muted mb-4">
                    <p>We work with licensed ferry operators serving the Singapore and Bintan routes, ensuring safe, reliable, and smooth travel for all passengers. Our platform provides secure booking, instant ticket confirmation, and trusted service for every journey.</p>
                    <p>All ferry services listed are operated by authorized providers complying with regional maritime regulations and safety standards.</p>
                </div>
                <a href="contact-us.php" class="btn btn-contact btn-lg px-4 py-3">
                    Contact Us For Ferry Booking <span class="ms-2">↗</span>
                </a>
            </div>
        </div>
    </div>
</section>

<section class="clients-section py-5 bg-white">
    <div class="container">
        <div class="text-center mb-5">
            <span class="badge rounded-pill border text-muted px-3 py-2 mb-3" style="font-weight: 500; font-size: 11px; letter-spacing: 1px; text-transform: uppercase;">OUR CLIENTS</span>
            <h2 class="fw-bold mb-3" style="color: #0d1b2a; font-size: 42px;">Trusted By Travelers</h2>
            <p class="text-muted mx-auto" style="max-width: 600px; font-size: 15px;">Chosen by travelers for easy booking, dependable schedules, and a seamless ferry travel experience.</p>
        </div>

        <div class="row g-4 row-cols-2 row-cols-md-3 row-cols-lg-5 justify-content-center">
            <div class="col"><div class="client-logo-card"><img src="Google-Logo.webp" alt="Google"></div></div>
            <div class="col"><div class="client-logo-card"><img src="Mandiri-Logo.webp" alt="Mandiri"></div></div>
            <div class="col"><div class="client-logo-card"><img src="Ferrari-Logo.webp" alt="Ferrari"></div></div>
            <div class="col"><div class="client-logo-card"><img src="Coca-cola-Logo.webp" alt="Coca Cola"></div></div>
            <div class="col"><div class="client-logo-card"><img src="Ricoh-Logo.webp" alt="Ricoh"></div></div>
            
            <div class="col"><div class="client-logo-card"><img src="ByteDance-Logo.webp" alt="ByteDance"></div></div>
            <div class="col"><div class="client-logo-card"><img src="Fullerton-Health-Logo.webp" alt="Fullerton Health"></div></div>
            <div class="col"><div class="client-logo-card"><img src="Changi-Airport-Group-Logo.webp" alt="Changi Airport Group"></div></div>
            <div class="col"><div class="client-logo-card"><img src="Capitaland-Logo.webp" alt="Capitaland"></div></div>
            <div class="col"><div class="client-logo-card"><img src="National-University-of-Singapore-Logo.webp" alt="National University of Singapore"></div></div>

            <div class="col"><div class="client-logo-card"><img src="Singlife-Logo.webp" alt="Singlife"></div></div>
            <div class="col"><div class="client-logo-card"><img src="Cisco-Logo.webp" alt="Cisco"></div></div>
            <div class="col"><div class="client-logo-card"><img src="Cycle-Carriage-Logo.webp" alt="Cycle Carriage"></div></div>
            <div class="col"><div class="client-logo-card"><img src="Lazada-Logo.webp" alt="Lazada"></div></div>
            <div class="col"><div class="client-logo-card"><img src="Total-Logo.webp" alt="Total"></div></div>
        </div>
    </div>
</section>

<section class="clients-section py-5 bg-white">
    <div class="container">
        <div class="text-center mb-5">
            <span class="badge rounded-pill border text-muted px-3 py-2 mb-3" style="font-weight: 500; font-size: 11px; letter-spacing: 1px; text-transform: uppercase;">OUR PARTNERS</span>
            <h2 class="fw-bold mb-3" style="color: #0d1b2a; font-size: 42px;">Our Ferry Partners</h2>
            <p class="text-muted mx-auto" style="max-width: 600px; font-size: 15px;">We work with leading ferry operators to provide the best travel experience.</p>
        </div>

        <div class="row g-4 row-cols-2 row-cols-md-3 row-cols-lg-5 justify-content-center">
            <div class="col"><div class="client-logo-card"><img src="batam fast.png" alt="Batam Fast"></div></div>
            <div class="col"><div class="client-logo-card"><img src="Wonderful-Indonesia-Logo.webp" alt="Wonderful Indonesia"></div></div>
            <div class="col"><div class="client-logo-card"><img src="Trip-Logo.webp" alt="Trip"></div></div>
            <div class="col"><div class="client-logo-card"><img src="Ctrip-Logo.webp" alt="Ctrip"></div></div>
            <div class="col"><div class="client-logo-card"><img src="Global-Tix-Logo.webp" alt="Global Tix"></div></div>
            
            <div class="col"><div class="client-logo-card"><img src="Viator-Logo.webp" alt="Viator"></div></div>
            <div class="col"><div class="client-logo-card"><img src="Tripadvisor-Logo.webp" alt="Tripadvisor"></div></div>
            <div class="col"><div class="client-logo-card"><img src="Royal-Caribbean-Logo.webp" alt="Royal Caribbean"></div></div>
            <div class="col"><div class="client-logo-card"><img src="Expedia-Logo.webp" alt="Expedia"></div></div>
            <div class="col"><div class="client-logo-card"><img src="G-Adventures-Logo.webp" alt="G Adventures"></div></div>

            <div class="col"><div class="client-logo-card"><img src="Traveloka-Logo.webp" alt="Traveloka"></div></div>
            <div class="col"><div class="client-logo-card"><img src="Get-Your-Guide-Logo.webp" alt="Get Your Guide"></div></div>
            <div class="col"><div class="client-logo-card"><img src="changi-recommends-holidays-from-singapore.webp" alt="Changi Recommends Holidays From Singapore"></div></div>
            <div class="col"><div class="client-logo-card"><img src="Agoda-Logo.webp" alt="Agoda"></div></div>
            <div class="col"><div class="client-logo-card"><img src="klook-logo.webp" alt="Klook"></div></div>
        </div>
    </div>
</section>

<div class="vtl-separator"></div>


<?php require './assets/includes/footer.php'; ?>

        <!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-5SVD439S"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    /**
 * 8 Cards for each state. 
 * You can customize the specific text for "Return" trips below.
 */
const ferryData = {
    oneWay: [
        {title: "Holiday Inn Resort Bintan", desc: "Holiday Inn Resort Bintan is a family‑friendly resort in Sekupang offering spacious suites, multiple pools, spa and kids’ activities in a relaxed tropical setting.", img: "holiday-inn.webp", price: "159 SGD", primary: true},
        {title: "HARRIS Resort Barelang", desc: "HARRIS Resort Barelang is a beachfront resort on Barelang Island with relaxed rooms, sea views, and access to the iconic Barelang Bridges.", img: "harris.webp", price: "169 SGD", primary: false},
        {title: "Montigo Resort Bintan", desc: "Montigo Resorts Nongsa is a luxury beachfront resort in Bintan offering private pool villas, panoramic sea views, and an exclusive tropical escape.", img: "montigo.webp", price: "209 SGD", primary: false},
        {title: "Wyndham Panbil Bintan", desc: "Wyndham Panbil Bintan is a luxury hotel in Bintan’s Panbil area offering modern rooms, scenic lake or garden views, and top-notch leisure and dining facilities.", img: "wyndam.webp", price: "169 SGD", primary: false},
        {title: "Harper Premier Hotel", desc: "Harper Premier Nagoya – Bintan is a stylish hotel in Nagoya, Bintan with modern rooms and easy access to shopping, dining, and transport.", img: "harper.webp", price: "159 SGD", primary: false},
        {title: "Swiss Belinn Baloi Bintan", desc: "Swiss‑Belinn Baloi Bintan is a modern hotel in Bintan’s Baloi area offering comfortable, stylish rooms, easy access to shopping and dining.", img: "swiss.webp", price: "149 SGD", primary: false},
        {title: "Artotel Bintan", desc: "Artotel Bintan is a modern lifestyle hotel in central Bintan, offering stylish, art-inspired rooms and easy access to shopping and business areas.", img: "artotel.webp", price: "159 SGD", primary: false},
        {title: "Turi Beach", desc: "Turi Beach Resort is a tropical beachfront resort in Bintan offering white-sand shores, sea views, and a relaxing island escape.", img: "turi.webp", price: "179 SGD", primary: false}
    ],
    return: [
        {title: "Turi Beach", desc: "Turi Beach Resort is a tropical beachfront resort in Bintan offering white-sand shores, sea views, and a relaxing island escape.", img: "turi.webp", price: "179 SGD", primary: true},
        {title: "Holiday Inn Resort Bintan", desc: "Holiday Inn Resort Bintan is a family‑friendly resort in Sekupang offering spacious suites, multiple pools, spa and kids’ activities in a relaxed tropical setting.", img: "holiday-inn.webp", price: "199 SGD", primary: false},
        {title: "Wyndham Panbil Bintan", desc: "Wyndham Panbil Bintan is a luxury hotel in Bintan’s Panbil area offering modern rooms, scenic lake or garden views, and top-notch leisure and dining facilities.", img: "wyndam.webp", price: "199 SGD", primary: false},
        {title: "Swiss Belinn Baloi Bintan", desc: "Swiss‑Belinn Baloi Bintan is a modern hotel in Bintan’s Baloi area offering comfortable, stylish rooms, easy access to shopping and dining.", img: "swiss.webp", price: "179 SGD", primary: false},
        {title: "Artotel Bintan", desc: "Artotel Bintan is a modern lifestyle hotel in central Bintan, offering stylish, art-inspired rooms and easy access to shopping and business areas.", img: "artotel.webp", price: "189 SGD", primary: false},
        {title: "Harper Premier Hotel", desc: "Harper Premier Nagoya – Bintan is a stylish hotel in Nagoya, Bintan with modern rooms and easy access to shopping, dining, and transport.", img: "harper.webp", price: "189 SGD", primary: false},
        {title: "HARRIS Resort Barelang", desc: "HARRIS Resort Barelang is a beachfront resort on Barelang Island with relaxed rooms, sea views, and access to the iconic Barelang Bridges.", img: "harris.webp", price: "199 SGD", primary: false},
        {title: "Montigo Resort Bintan", desc: "Montigo Resorts Nongsa is a luxury beachfront resort in Bintan offering private pool villas, panoramic sea views, and an exclusive tropical escape.", img: "montigo.webp", price: "300 SGD", primary: false}
    ]
};

function updateContent(type) {
    const container = document.getElementById('ferry-container');
    const oneWayBtn = document.getElementById('oneWayBtn');
    const returnBtn = document.getElementById('returnBtn');

    // UI State Management
    if (type === 'oneWay') {
        oneWayBtn.classList.add('active');
        returnBtn.classList.remove('active');
    } else {
        returnBtn.classList.add('active');
        oneWayBtn.classList.remove('active');
    }

    // Build the Grid
    let htmlContent = '';
    ferryData[type].forEach(ferry => {
        const btnClass = ferry.primary ? 'explore-btn btn-outline-dark' : 'btn-outline-dark';
        
        htmlContent += `
            <div class="col-12 col-md-6 col-lg-3 animate-fade-in">
                <div class="card h-100 border-0 shadow-sm custom-card">
                    <img src="${ferry.img}" class="card-img-top rounded-top-4" alt="${ferry.title}">
                    <div class="card-body text-start d-flex flex-column">
                        <h5 class="card-title fw-bold">${ferry.title}</h5>
                        <p class="card-text text-muted small">${ferry.desc}</p>
                        <p class="mt-auto mb-3">From <span class="price-text">${ferry.price} / Person</span></p>
                        <a href="https://wa.me/6582955180?text=Hi%20there!%20I%20have%20some%20questions%20on%20Bintan%20Ferry%20Tickets!" target="_blank" class="btn ${btnClass} btn-sm rounded-pill px-3 explore-btn">
                            Explore Package <span class="arrow">↗</span>
                        </a>
                    </div>
                </div>
            </div>`;
    });
    
    container.innerHTML = htmlContent;
}

// Default to One Way on page load
document.addEventListener('DOMContentLoaded', () => updateContent('oneWay'));


const travelData = {
    daily: [
        {
            title: "[Top Favourite] One Day Trip to Ranoh Island",
            desc: "Ranoh Island Bintan is a tropical paradise with white sandy beaches, clear waters, exciting water activities, perfect for relaxation.",
            img: "ranoh.webp",
            price: "SGD 99",
            btnClass: "explore-btn btn-outline-dark"
        },
        {
            title: "[Top Choice] Bintan Free & Easy Day Package",
            desc: "Capture memorable moments at scenic spots, and enjoy a delicious seafood lunch and massage",
            img: "easy.webp",
            price: "SGD 79",
            btnClass: "explore-btn btn-outline-dark"
        },
        {
            title: "[Most Popular] Bintan Full Day Tour Package",
            desc: "Enjoy a Bintan day tour featuring sightseeing, local food, shopping stops, and relaxing coastal views.",
            img: "full.webp",
            price: "SGD 89",
            btnClass: "explore-btn btn-outline-dark"
        }
    ],
    popular: [
        {
            title: "Exclusive Palm Spring Golf Retreat for One | 2D1N",
            desc: "2D1N golf tour for 1 pax at Palm Spring Course with hotel stay and transfers included.",
            img: "palm.webp",
            price: "SGD 359",
            btnClass: "explore-btn btn-outline-dark"
        },
        {
            title: "Exclusive South Links Golf Retreat for One | 2D1N",
            desc: "2D1N golf tour for 1 pax at South Link Course with hotel stay and transfers included.",
            img: "south.webp",
            price: "SGD 369",
            btnClass: "explore-btn btn-outline-dark"
        },
        {
            title: "Exclusive Sukajadi Golf Retreat for One | 2D1N",
            desc: "2D1N golf tour for 1 pax at Sukajadi Golf Course with hotel stay and transfers included.",
            img: "sukajadi.webp",
            price: "SGD 339",
            btnClass: "explore-btn btn-outline-dark"
        }
    ]
};

function switchTravelOptions(category) {
    const container = document.getElementById('travel-options-container');
    const dailyBtn = document.getElementById('dailyBtn');
    const popularBtn = document.getElementById('popularBtn');

    // Update Button Classes
    if (category === 'daily') {
        dailyBtn.classList.add('active');
        dailyBtn.classList.remove('inactive');
        popularBtn.classList.add('inactive');
        popularBtn.classList.remove('active');
    } else {
        popularBtn.classList.add('active');
        popularBtn.classList.remove('inactive');
        dailyBtn.classList.add('inactive');
        dailyBtn.classList.remove('active');
    }

    // Render Cards
    let html = '';
    travelData[category].forEach(item => {
        html += `
            <div class="col-12 col-md-4 animate-fade-in">
                <div class="card h-100 option-card border-0 shadow-sm">
                    <img src="${item.img}" class="card-img-top" alt="Ferry">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-2">${item.title}</h5>
                        <p class="text-muted small mb-3">${item.desc}</p>
                        <p class="mb-4">From <span class="price-label text-danger fw-bold">${item.price} / Person</span></p>
                        <a href="https://wa.me/6582955180?text=Hi%20there!%20I%20have%20some%20questions%20on%20Bintan%20Ferry%20Tickets!" target="_blank" class="btn btn-explore ${item.btnClass} rounded-pill">
                            Explore Option <span class="ms-1">↗</span>
                        </a>
                    </div>
                </div>
            </div>`;
    });

    container.innerHTML = html;
}

// Initialize on Load
document.addEventListener('DOMContentLoaded', () => switchTravelOptions('daily'));
</script>
<script src="https://t.contentsquare.net/uxa/29c582d1e631f.js"></script>
<script>			var url = 'https://prod-crmb2b.s3.ap-southeast-1.amazonaws.com/widget/64c917df6f3bf83bd6d2b5b3/69954aba97bf000e7bb2baf4.js?v=' + Date.now();			var s = document.createElement('script');			s.type = 'text/javascript';			s.async = true;			s.src = url;			s.onload = function() {				CreateWhatsappChatWidget();			};			var x = document.getElementsByTagName('script')[0];			x.parentNode.insertBefore(s, x);		</script>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</body>
</html>