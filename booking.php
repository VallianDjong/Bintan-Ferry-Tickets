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
            justify-content: center;
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
                    url('adventure.webp');
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
            background-color: rgba(0, 0, 0, 0.45); /* Darken the video */
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

        /* Add these to your existing CSS */
    .experience-section {
        background-color: #ffffff;
    }

    .btn-primary-red {
        background-color: #359DD7;
        color: white;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        transition: 0.3s;
    }

    .btn-primary-red:hover {
        background-color: #d13d37;
        color: white;
    }

    .btn-outline-light-custom {
        background-color: #f8fafc;
        color: #64748b;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        font-weight: 600;
    }

    .video-card {
        background: white;
        border-radius: 15px;
        overflow: hidden;
        border: 1px solid #f1f5f9;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        transition: transform 0.3s ease;
    }

    .video-card:hover {
        transform: translateY(-5px);
    }

    .video-thumbnail-wrapper {
        position: relative;
        overflow: hidden;
    }

    .large-card .video-thumbnail-wrapper {
        height: 350px;
    }

    .small-card .video-thumbnail-wrapper {
        height: 180px;
    }

    .video-thumb {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .play-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.1);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .play-button {
        width: 60px;
        height: 60px;
        background: #ff0000;
        border-radius: 50%;
        position: relative;
        cursor: pointer;
    }

    .play-button::after {
        content: "";
        position: absolute;
        top: 50%;
        left: 55%;
        transform: translate(-50%, -50%);
        border-style: solid;
        border-width: 10px 0 10px 18px;
        border-color: transparent transparent transparent white;
    }

    .play-button.small {
        width: 40px;
        height: 40px;
    }

    .play-button.small::after {
        border-width: 6px 0 6px 11px;
    }

    .video-header-info {
        position: absolute;
        top: 15px;
        left: 15px;
        display: flex;
        align-items: center;
        color: white;
        font-size: 14px;
        text-shadow: 0 2px 4px rgba(0,0,0,0.5);
    }

    .small-video-title {
        font-size: 14px;
        color: #1a222d;
        line-height: 1.4;
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

    .routes-section {
        background-color: #ffffff;
    }

    .route-card {
        background-color: white;
        border-radius: 20px;
        border: 1px solid #f1f5f9;
        overflow: hidden;
        height: 100%;
        transition: all 0.3s ease;
        text-align: center;
        padding: 12px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.03);
    }

    .route-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 15px 35px rgba(0,0,0,0.08);
    }

    .route-img-wrapper {
        width: 100%;
        height: 200px;
        border-radius: 15px;
        overflow: hidden;
        margin-bottom: 20px;
    }

    .route-img-wrapper img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .route-content {
        padding: 0 15px 15px;
    }

    .route-title {
        font-weight: 700;
        font-size: 18px;
        color: #0d1b2a;
        margin-bottom: 12px;
    }

    .route-text {
        font-size: 13px;
        color: #94a3b8;
        line-height: 1.6;
        margin-bottom: 20px;
        min-height: 65px; /* Ensures buttons align even with varying text lengths */
    }

    /* Active Red Button */
    .btn-primary-red {
        background-color: #359DD7;
        color: white;
        border: none;
        border-radius: 12px;
        font-size: 13px;
        font-weight: 600;
    }

    /* Inactive Outline Button */
    .btn-outline-route {
        background-color: transparent;
        color: #1a222d;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        font-size: 13px;
        font-weight: 600;
        transition: 0.2s;
    }

    .btn-outline-route:hover {
        background-color: #359DD7;
        color: white;
    }

    .journey-card {
        position: relative;
        border-radius: 20px;
        overflow: visible; /* Needed for the overlapping box */
        height: 320px; /* Fixed height for image area */
        margin-bottom: 80px; /* Space for the overlapping content */
    }

    .journey-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 20px;
    }

    .journey-overlay-box {
        position: absolute;
        bottom: -60px; /* Overlaps bottom edge */
        left: 10px;
        right: 10px;
        background-color: #359DD7;
        color: white;
        padding: 20px;
        border-radius: 15px;
        text-align: center;
        box-shadow: 0 10px 25px rgba(234, 77, 70, 0.3);
        transition: transform 0.3s ease;
    }

    .journey-card:hover .journey-overlay-box {
        transform: translateY(-5px);
    }

    .journey-overlay-box h6 {
        font-weight: 700;
        font-size: 17px;
        margin-bottom: 8px;
    }

    .journey-overlay-box p {
        font-size: 12px;
        line-height: 1.5;
        margin-bottom: 0;
        opacity: 0.9;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .journey-card {
            margin-bottom: 100px;
        }
    }

    /* Feature List Styling */
    .feature-icon-box {
        background-color: #359DD7;
        color: white;
        width: 45px;
        height: 45px;
        min-width: 45px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
    }

    .feature-item {
        transition: background-color 0.2s ease;
    }

    .feature-item h6 {
        color: #0d1b2a;
        font-size: 16px;
    }

    /* Image Frame Effects */
    .image-frame-wrapper {
        padding: 20px;
        perspective: 1000px;
    }

    .tilted-frame {
        position: relative;
        background: white;
        padding: 15px;
        border-radius: 10px;
        box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        transform: rotate(-2deg); /* The slight tilt seen in image */
        transition: transform 0.5s ease;
    }

    .tilted-frame:hover {
        transform: rotate(0deg);
    }

    .main-frame-img {
        width: 100%;
        border-radius: 5px;
        display: block;
    }

    .mini-frame {
        position: absolute;
        bottom: 20px;
        left: -30px;
        width: 140px;
        background: white;
        padding: 8px;
        border-radius: 8px;
        box-shadow: 0 10px 20px rgba(0,0,0,0.15);
        border: 4px solid #1a222d; /* Blueish border from image */
    }

    .mini-frame img {
        width: 100%;
        border-radius: 4px;
    }

    @media (max-width: 991px) {
        .image-frame-wrapper {
            max-width: 500px;
            margin: 0 auto;
        }
        .mini-frame {
            width: 100px;
            left: 10px;
        }
    }

    /* CTA Banner Styling */
    .cta-banner {
        background: linear-gradient(rgba(13, 27, 42, 0.85), rgba(13, 27, 42, 0.85)), 
                    url('adventure.webp');
        background-size: cover;
        background-position: center;
        border-radius: 30px;
        padding: 100px 20px 160px; /* Extra bottom padding for overlap */
        position: relative;
    }

    .play-circle {
        width: 60px;
        height: 60px;
        background: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto;
        color: #359DD7;
        font-size: 30px;
        cursor: pointer;
        transition: transform 0.3s ease;
    }

    .play-circle:hover {
        transform: scale(1.1);
    }

    /* Overlapping Card Row */
    .feature-cards-row {
        margin-top: -100px; /* Pulls cards up into the banner */
        position: relative;
        z-index: 5;
    }

    .info-card {
        background: white;
        padding: 40px 30px;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        height: 100%;
        border: 1px solid #f1f5f9;
    }

    .info-icon-box {
        width: 60px;
        height: 60px;
        background-color: #359DD7;
        color: white;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
    }

    .info-card h5 {
        color: #0d1b2a;
        font-size: 18px;
    }

    .info-card p {
        line-height: 1.6;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .feature-cards-row {
            margin-top: -60px;
        }
        .cta-banner {
            padding: 60px 20px 120px;
        }
    }

    .trip-img-card {
        border-radius: 25px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0,0,0,0.05);
    }

    .trip-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        min-height: 300px;
    }

    .trip-text-card {
        background-color: #f8fafc;
        padding: 60px;
        border-radius: 25px;
        border: 1px solid #f1f5f9;
    }

    /* Responsive adjustments */
    @media (max-width: 991px) {
        .trip-text-card {
            padding: 30px;
        }
    }

    .image-frame-wrapper {
        padding: 20px;
    }

    .tilted-frame {
        background: white;
        padding: 15px;
        border-radius: 10px;
        box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        transform: rotate(-3deg); /* Slight tilt to match image 9 */
        transition: transform 0.4s ease;
    }

    .tilted-frame:hover {
        transform: rotate(0deg);
    }

    .main-frame-img {
        width: 100%;
        border-radius: 5px;
        display: block;
    }

    /* Badge and Button adjustments to match image_2d621c */
    .badge.border {
        border-color: #cbd5e1 !important;
        color: #64748b !important;
    }

    /* Styling for Destinations Section */
    .destination-card {
        background-color: white;
        border-radius: 20px;
        border: 1px solid #f1f5f9;
        padding: 15px;
        height: 100%;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(0,0,0,0.02);
    }

    .destination-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 12px 30px rgba(0,0,0,0.07);
    }

    .dest-img-wrapper {
        width: 100%;
        height: 240px;
        border-radius: 15px;
        overflow: hidden;
        margin-bottom: 25px;
    }

    .dest-img-wrapper img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .dest-content {
        padding: 0 10px 10px;
    }

    .dest-content h4 {
        color: #0d1b2a;
        font-size: 22px;
    }

    .dest-content p {
        line-height: 1.6;
    }

    /* Reuse the button classes from previous sections */
    .btn-primary-red {
        background-color: #359DD7;
        color: white;
        border: none;
        border-radius: 12px;
        font-size: 14px;
        font-weight: 600;
        transition: 0.3s;
    }

    .btn-outline-route {
        background-color: transparent;
        color: #1a222d;
        border: 1px solid #cbd5e1;
        border-radius: 12px;
        font-size: 14px;
        font-weight: 600;
    }

    .btn-outline-route:hover {
        background-color: #359DD7;
        color: white;
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

    .video-card {
        background: white;
        border-radius: 15px;
        overflow: hidden;
        border: 1px solid #f1f5f9;
        box-shadow: 0 4px 15px rgba(0,0,0,0.03);
        height: 100%;
        transition: transform 0.3s ease;
    }

    .video-card:hover {
        transform: translateY(-5px);
    }

    .video-thumb-wrapper {
        position: relative;
        width: 100%;
        height: 320px; /* Large height */
        overflow: hidden;
    }

    .small-thumb {
        height: 200px; /* Small height */
    }

    .video-thumb-wrapper img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    /* Red Play Button Overlay */
    .play-button-overlay {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 60px;
        height: 60px;
        background: #ff0000;
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 30px;
        box-shadow: 0 0 20px rgba(255,0,0,0.4);
    }

    .play-button-overlay.sm {
        width: 40px;
        height: 40px;
        font-size: 20px;
    }

    /* Small Label in corner */
    .video-label {
        position: absolute;
        top: 15px;
        left: 15px;
        color: white;
        font-size: 13px;
        font-weight: 500;
        text-shadow: 1px 1px 3px rgba(0,0,0,0.5);
    }

    .video-label.sm {
        font-size: 11px;
        top: 10px;
        left: 10px;
    }

    .video-info h5 {
        color: #0d1b2a;
        font-size: 20px;
    }

    .video-info p {
        color: #0d1b2a;
    }

    /* Blog Card Styling */
    .blog-card {
        background-color: white;
        border-radius: 20px;
        border: 1px solid #f1f5f9;
        overflow: hidden;
        height: 100%;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(0,0,0,0.02);
    }

    .blog-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 12px 30px rgba(0,0,0,0.07);
    }

    .blog-img-wrapper {
        width: 100%;
        height: 220px;
        overflow: hidden;
    }

    .blog-img-wrapper img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .blog-content {
        padding: 25px;
    }

    .blog-date {
        color: #359DD7;
        font-size: 13px;
        font-weight: 500;
    }

    .blog-content h4 {
        color: #0d1b2a;
        font-size: 20px;
        line-height: 1.4;
    }

    .blog-content p {
        line-height: 1.6;
    }

    /* Primary and Outline Button Styles (Consistent with previous sections) */
    .btn-primary-red {
        background-color: #359DD7;
        color: white;
        border: none;
        border-radius: 12px;
        font-size: 14px;
        font-weight: 600;
    }

    .btn-outline-route {
        background-color: transparent;
        color: #1a222d;
        border: 1px solid #cbd5e1;
        border-radius: 12px;
        font-size: 14px;
        font-weight: 600;
    }

    .contact-form-card {
        background-color: #f8fafc;
        padding: 40px;
        border-radius: 25px;
        border: 1px solid #f1f5f9;
    }

    .contact-icon {
        width: 45px;
        height: 45px;
        background-color: #359DD7;
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
    }

    .form-control, .form-select, .input-group-text {
        border-radius: 10px;
        padding: 12px;
        border: 1px solid #e2e8f0;
        font-size: 14px;
    }

    .form-control:focus, .form-select:focus {
        border-color: #359DD7;
        box-shadow: 0 0 0 0.2rem rgba(234, 77, 70, 0.15);
    }

    .contact-methods h5, .follow-us h5 {
        color: #0d1b2a;
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
        <video autoplay loop muted playsinline id="hero-video">
            <source src="https://sample-videos.com/video123/mp4/720/big_buck_bunny_720p_1mb.mp4" type="video/mp4">
        </video>
        <div class="hero-overlay"></div>
        <div class="container hero-content-wrapper">
            <div class="row align-items-center">
                <div class="col-lg-6 col-12 hero-text mb-5 mb-lg-0">
                    <h1 class="hero-title">Get Your Bintan Team Building Quote Here</h1>
                    <p class="hero-description">The best rates for Bintan hotels, tours and team building packages</p>
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
                                    <label for="travel_date" class="form-label">When you going?<span class="required-star">*</span></label>
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
                                        <option value="Corporate Group">Corporate Group</option>
                                        <option value="Community Centre">Community Centre</option>
                                        <option value="Church Retreat">Church Retreat</option>
                                        <option value="Family">Family</option>
                                        <option value="Group of Friends">Group of Friends</option>
                                        <option value="Others">Others</option>
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

        <section class="experience-section py-5">
    <div class="container">
        <div class="text-center mb-5">
            <span class="badge rounded-pill border text-muted px-3 py-2 mb-3" style="font-weight: 500; font-size: 11px; letter-spacing: 1px;">REAL TRAVEL EXPERIENCE</span>
            <h2 class="fw-bold mb-3" style="color: #0d1b2a; font-size: 36px;">Successful Bintan <br>Teambuilding Experience</h2>
            <p class="text-muted mx-auto" style="max-width: 800px; font-size: 14px; line-height: 1.6;">
                Experience smooth and hassle-free ferry journeys from Singapore to Bintan with trusted operators, comfortable seating, and efficient boarding. 
                From booking to arrival, every step is designed to ensure a convenient and enjoyable travel experience.
            </p>
            
            <!--<div class="d-flex justify-content-center gap-3 mt-4">
                 <button class="btn btn-primary-red px-4 py-2">Singapore Tours</button>
                 <button class="btn btn-outline-light-custom px-4 py-2">Batam Tours</button>
             </div> -->
        </div>

        <div class="row g-4">
            <div class="col-lg-6">
                <div class="video-card large-card h-100">
                   <iframe 
                    src="https://www.youtube.com/embed/EA-5_vkMHJU" 
                    title="YouTube video player" 
                    frameborder="0" 
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" 
                    allowfullscreen
                    style="width: 100%; aspect-ratio: 16/9;">
                </iframe>
                    <div class="p-4">
                        <h4 class="fw-bold mb-4">2D1N in Bintan with ISO TEAM</h4>
                        <a href="https://youtube.com/@vtltravel?si=ZTW34HgCutflQJ1d" targe="_blank" class="btn btn-primary-red">Watch More Videos ↗</a>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="video-card small-card">
                            <iframe 
                    src="https://www.youtube.com/embed/yJqwCEwPRvI?si=o6EWXjz_FXcuBtT6" 
                    title="YouTube video player" 
                    frameborder="0" 
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" 
                    allowfullscreen
                    style="width: 100%; aspect-ratio: 16/9;">
                </iframe>
                            <div class="p-3">
                                <h6 class="fw-bold small-video-title">3D2N Defence Collective Pte Ltd</h6>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="video-card small-card">
                            <iframe 
                    src="https://www.youtube.com/embed/1CUC6gqqsA0" 
                    title="YouTube video player" 
                    frameborder="0" 
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" 
                    allowfullscreen
                    style="width: 100%; aspect-ratio: 16/9;">
                </iframe>
                            <div class="p-3">
                                <h6 class="fw-bold small-video-title">Day Trip in Bintan with Orange Tee</h6>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="video-card small-card">
                            <iframe 
                    src="https://www.youtube.com/embed/ZvzT4FAiflE" 
                    title="YouTube video player" 
                    frameborder="0" 
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" 
                    allowfullscreen
                    style="width: 100%; aspect-ratio: 16/9;">
                </iframe>
                            <div class="p-3">
                                <h6 class="fw-bold small-video-title">2D1N ITS Science & Medical Pte Ltd</h6>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="video-card small-card">
                            <iframe 
                    src="https://www.youtube.com/embed/1G2SmuZumis" 
                    title="YouTube video player" 
                    frameborder="0" 
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" 
                    allowfullscreen
                    style="width: 100%; aspect-ratio: 16/9;">
                </iframe>
                            <div class="p-3">
                                <h6 class="fw-bold small-video-title">3D2N Bintan Asian Healthcare Pte Ltd</h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="services-section py-5 bg-white">
    <div class="container">
        <div class="text-center mb-5">
            <span class="badge rounded-pill border text-muted px-3 py-2 mb-3" style="font-weight: 500; font-size: 11px; letter-spacing: 1px; text-transform: uppercase;">WHAT WE OFFER</span>
            <h2 class="fw-bold mb-4" style="color: #0d1b2a; font-size: 42px;">All Inclusive Bintan <br> Teambuilding Services</h2>
        </div>

        <div class="row g-4">
            <div class="col-xl-3 col-lg-4 col-md-6">
                <div class="service-card">
                    <div class="service-img-wrapper">
                        <img src="accomodation.webp" alt="Online Booking">
                    </div>
                    <div class="service-content">
                        <h5 class="service-title">Accomodation</h5>
                        <p class="service-text">We work closely with the hotels and resorts in Bintan to obtain the best rates for our customers.</p>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-lg-4 col-md-6">
                <div class="service-card">
                    <div class="service-img-wrapper">
                        <img src="meeting.webp" alt="Routes">
                    </div>
                    <div class="service-content">
                        <h5 class="service-title">Meeting Rooms</h5>
                        <p class="service-text">A variety of meeting rooms are available for groups of different sizes and needs.</p>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-lg-4 col-md-6">
                <div class="service-card">
                    <div class="service-img-wrapper">
                        <img src="team.webp" alt="E-Tickets">
                    </div>
                    <div class="service-content">
                        <h5 class="service-title">Team Building</h5>
                        <p class="service-text">We offer a wide range of team-building activities to suit your group’s needs.</p>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-lg-4 col-md-6">
                <div class="service-card">
                    <div class="service-img-wrapper">
                        <img src="meals.webp" alt="Schedules">
                    </div>
                    <div class="service-content">
                        <h5 class="service-title">Meals</h5>
                        <p class="service-text">Savour dining experiences tailored to your preferences, with halal and vegetarian options available.</p>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-lg-4 col-md-6">
                <div class="service-card">
                    <div class="service-img-wrapper">
                        <img src="ferry_1.png" alt="Operators">
                    </div>
                    <div class="service-content">
                        <h5 class="service-title">Ferry Tickets</h5>
                        <p class="service-text">We help to book your ferry tickets to ensure a smooth and hassle-free journey for your entire team.</p>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-lg-4 col-md-6">
                <div class="service-card">
                    <div class="service-img-wrapper">
                        <img src="transportation.webp" alt="Payments">
                    </div>
                    <div class="service-content">
                        <h5 class="service-title">Transportation</h5>
                        <p class="service-text">We offer transport from the ferry terminal to hotels, resorts, and Desaru tour destinations.</p>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-lg-4 col-md-6">
                <div class="service-card">
                    <div class="service-img-wrapper">
                        <img src="dinner.webp" alt="Group Booking">
                    </div>
                    <div class="service-content">
                        <h5 class="service-title">Gala Dinner</h5>
                        <p class="service-text">We organise gala dinner, including live bands, emcee, and customised event arrangements.</p>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-lg-4 col-md-6">
                <div class="service-card">
                    <div class="service-img-wrapper">
                        <img src="request.webp" alt="Support">
                    </div>
                    <div class="service-content">
                        <h5 class="service-title">Any Other Requests?</h5>
                        <p class="service-text">If you have any requests, feel free to let us know. We will customise a plan to suit your needs.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- <section class="routes-section py-5 bg-white">
    <div class="container">
        <div class="text-center mb-5">
            <span class="badge rounded-pill border text-muted px-3 py-2 mb-3" style="font-weight: 500; font-size: 11px; letter-spacing: 1px; text-transform: uppercase;">TOP ROUTES</span>
            <h2 class="fw-bold mb-4" style="color: #0d1b2a; font-size: 42px;">Batam Ferry Routes <br> We Offer</h2>
        </div>

        <div class="row g-4">
            <div class="col-lg-4 col-md-6">
                <div class="route-card">
                    <div class="route-img-wrapper">
                        <img src="ferry_6.png" alt="Batam Centre">
                    </div>
                    <div class="route-content">
                        <h5 class="route-title">HarbourFront → Batam Centre</h5>
                        <p class="route-text">Most popular route offering frequent daily departures, fast travel time, smooth boarding, and convenient arrival access.</p>
                        <a href="schedule.php" class="btn btn-primary-red px-4 py-2 mt-2">View Schedule ↗</a>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="route-card">
                    <div class="route-img-wrapper">
                        <img src="ferry_1.png" alt="Sekupang">
                    </div>
                    <div class="route-content">
                        <h5 class="route-title">HarbourFront → Sekupang</h5>
                        <p class="route-text">Ideal route for business travelers, providing efficient connections, shorter queues, and quick access to key local areas.</p>
                        <a href="schedule.php" class="btn btn-outline-route px-4 py-2 mt-2">View Schedule ↗</a>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="route-card">
                    <div class="route-img-wrapper">
                        <img src="ferry_2.png" alt="Nongsa">
                    </div>
                    <div class="route-content">
                        <h5 class="route-title">Tanah Merah → Nongsa</h5>
                        <p class="route-text">Perfect for resort travelers, offering direct access to luxury stays, beaches, and relaxing leisure destinations in Batam.</p>
                        <a href="schedule.php" class="btn btn-outline-route px-4 py-2 mt-2">View Schedule ↗</a>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="route-card">
                    <div class="route-img-wrapper">
                        <img src="ferry_3.png" alt="Waterfront">
                    </div>
                    <div class="route-content">
                        <h5 class="route-title">Batam Centre → HarbourFront</h5>
                        <p class="route-text">Convenient route with direct access to waterfront resorts, entertainment spots, and comfortable travel experience for all passengers.</p>
                        <a href="schedule.php" class="btn btn-outline-route px-4 py-2 mt-2">View Schedule ↗</a>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="route-card">
                    <div class="route-img-wrapper">
                        <img src="ferry_4.png" alt="Sekupang Return">
                    </div>
                    <div class="route-content">
                        <h5 class="route-title">Sekupang → HarbourFront</h5>
                        <p class="route-text">Reliable return option for travelers, offering comfortable seating, efficient boarding, and quick arrival at HarbourFront terminal.</p>
                        <a href="schedule.php" class="btn btn-outline-route px-4 py-2 mt-2">View Schedule ↗</a>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="route-card">
                    <div class="route-img-wrapper">
                        <img src="ferry_5.png" alt="Nongsa Return">
                    </div>
                    <div class="route-content">
                        <h5 class="route-title">Nongsa → Tanah Merah</h5>
                        <p class="route-text">Best return route for resort travelers, providing direct access, relaxed travel experience, and convenient Singapore arrival terminal.</p>
                        <a href="schedule.php" class="btn btn-outline-route px-4 py-2 mt-2">View Schedule ↗</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section> -->

<section class="journey-section py-5 bg-white">
    <div class="container">
        <div class="text-center mb-5">
            <span class="badge rounded-pill border text-muted px-3 py-2 mb-3" style="font-weight: 500; font-size: 11px; letter-spacing: 1px; text-transform: uppercase;">YOUR TRIP</span>
            <h2 class="fw-bold mb-4" style="color: #0d1b2a; font-size: 42px;">Enjoy Your Bintan <br>Teambuilding Journey</h2>
        </div>

        <div class="row g-4">
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="journey-card">
                    <img src="telematch.webp" alt="Beach" class="journey-img">
                    <div class="journey-overlay-box">
                        <h6>Telematch Games</h6>
                        <p>Fun and engaging games to promote teamwork. We can arrange this at team building area / hotel function room</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-4">
                <div class="journey-card">
                    <img src="trail.webp" alt="Dining" class="journey-img">
                    <div class="journey-overlay-box">
                        <h6>ATV Forest Trail</h6>
                        <p>Outdoor ATV team building with safe guidance, adrenaline-filled fun, suitable for beginners with no prior experience</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-4">
                <div class="journey-card">
                    <img src="airsoft.webp" alt="Spa" class="journey-img">
                    <div class="journey-overlay-box">
                        <h6>Indoor Airsoft Arena</h6>
                        <p>Exciting team-building experience, blending tactical gameplay with strategy and collaboration</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-4">
                <div class="journey-card">
                    <img src="paintball.webp" alt="Getaway" class="journey-img">
                    <div class="journey-overlay-box">
                        <h6>Paintball</h6>
                        <p>Load paint-filled bullets and compete in an action-packed game, with a safety briefing conducted beforehand</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-4">
                <div class="journey-card">
                    <img src="beach.webp" alt="Water" class="journey-img">
                    <div class="journey-overlay-box">
                        <h6>Beach Fire Beach Club</h6>
                        <p>Relax, unwind, and enjoy beachfront vibes, delicious drinks, and lively atmosphere at Bintan’s Blue Fire Beach Club</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-4">
                <div class="journey-card">
                    <img src="interactive.webp" alt="Golf" class="journey-img">
                    <div class="journey-overlay-box">
                        <h6>LED Interactive Game</h6>
                        <p>Ignite teamwork, challenge your team, and enjoy high-energy fun with Bintan’s LED interactive game experience</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-4">
                <div class="journey-card">
                    <img src="treasure.webp" alt="Nightlife" class="journey-img">
                    <div class="journey-overlay-box">
                        <h6>Jungle Warrior</h6>
                        <p>Discover, solve, and bond: a thrilling corporate jungle warrior in the heart of Panbil Nature Reserve</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-4">
                <div class="journey-card">
                    <img src="factory.webp" alt="Culture" class="journey-img">
                    <div class="journey-overlay-box">
                        <h6>70 fahrenheit Coffee Factory Tour</h6>
                        <p>Discover the art of coffee at Bintan’s 70 Fahrenheit Coffee Factory, from bean to cup</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="why-choose-section py-5 bg-white">
    <div class="container">
        <div class="row align-items-center">
            
            <div class="col-lg-6 mb-5 mb-lg-0">
                <div class="text-start mb-4">
                    <span class="badge rounded-pill border text-muted px-3 py-2 mb-3" style="font-weight: 500; font-size: 11px; letter-spacing: 1px; text-transform: uppercase;">WHY CHOOSE US</span>
                    <h2 class="fw-bold mb-4" style="color: #0d1b2a; font-size: 42px;">One Stop Solution For Bintan Teambuilding</h2>
                    <p class="text-muted" style="font-size: 14px; line-height: 1.6; max-width: 500px;">
                        Plan and manage your complete Bintan ferry journey effortlessly in one place, from comparing schedules to booking tickets securely, ensuring a smooth, reliable, and stress-free travel experience every time.
                    </p>
                </div>
                
                <div class="image-frame-wrapper">
                    <div class="tilted-frame">
                        <img src="big-content.webp" alt="Ferry View" class="main-frame-img">
                    </div>
                </div>
            </div>

            <div class="col-lg-6 ps-lg-5">
                <div class="features-list">
                    
                    <div class="feature-item d-flex align-items-start py-3 border-bottom">
                        <div class="feature-icon-box me-3">
                            <i class="bi bi-calendar-check"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-1">All-inclusive</h6>
                            <p class="mb-0 text-muted small">Hotels + Transportation + Tour Guide & Driver + Attraction Tickets + Daily Breakfast</p>
                        </div>
                    </div>

                    <div class="feature-item d-flex align-items-start py-3 border-bottom">
                        <div class="feature-icon-box me-3">
                            <i class="fas fa-ship"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-1">One Stop Solution</h6>
                            <p class="mb-0 text-muted small">Just book online & we will take care of your hotel, ferry, and transfer arrangements seamlessly and professionally</p>
                        </div>
                    </div>

                    <div class="feature-item d-flex align-items-start py-3 border-bottom">
                        <div class="feature-icon-box me-3">
                            <i class="bi bi-ticket-perforated"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-1">We Know Bintan Well</h6>
                            <p class="mb-0 text-muted small">We work closely with Bintan hotels/ferries to get the best rates and exclusive deals for you</p>
                        </div>
                    </div>

                    <div class="feature-item d-flex align-items-start py-3 border-bottom">
                        <div class="feature-icon-box me-3">
                            <i class="bi bi-shield-lock"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-1">Safety & Trust</h6>
                            <p class="mb-0 text-muted small">STB (Singapore Tourism Board) licensed tour agency with over 10 years of experience</p>
                        </div>
                    </div>

                    <div class="feature-item d-flex align-items-start py-3 border-bottom">
                        <div class="feature-icon-box me-3">
                            <i class="bi bi-headset"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-1">Dedicated Customer Support</h6>
                            <p class="mb-0 text-muted small">Get assistance anytime from our support team for bookings, changes, or travel-related queries</p>
                        </div>
                    </div>

                    <div class="feature-item d-flex align-items-start py-3">
                        <div class="feature-icon-box me-3">
                            <i class="bi bi-arrow-repeat"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-1">Good Bintan Rates</h6>
                            <p class="mb-0 text-muted small">We package popular Bintan resorts and hotels and offer you an attractive Bintan Package</p>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</section>

<!-- <section class="booking-cta-section py-5">
    <div class="container">
        <div class="cta-banner">
            <div class="cta-content text-center text-white">
                <h2 class="fw-bold mb-3" style="font-size: 42px;">Fast & Easy Batam Ferry <br> Booking</h2>
                <p class="mb-4 opacity-75">Book your ferry tickets online and travel smoothly between <br> Singapore and Batam.</p>
                
                <a href="https://www.youtube.com/watch?v=sNwRNuTvuWw" target="_blank" class="video-play-trigger">
    <div class="play-circle">
        <i class="bi bi-play-fill"></i>
    </div>
</a>
            </div>
        </div>

        <div class="row g-4 px-lg-5 feature-cards-row">
            <div class="col-md-4">
                <div class="info-card text-center">
                    <div class="info-icon-box mx-auto mb-4">
                        <i class="bi bi-geo-alt-fill"></i>
                    </div>
                    <h5 class="fw-bold mb-3">Local Support, No Outsource</h5>
                    <p class="text-muted small">To deliver reliable service, we handle all bookings with our in-house team, ensuring consistent support, trusted assistance, and a smooth ferry booking experience for every traveler.</p>
                </div>
            </div>

            <div class="col-md-4">
                <div class="info-card text-center">
                    <div class="info-icon-box mx-auto mb-4">
                        <i class="bi bi-grid-fill"></i>
                    </div>
                    <h5 class="fw-bold mb-3">One Stop Ferry Solution</h5>
                    <p class="text-muted small">We provide a complete ferry booking experience, including route selection, schedule comparison, ticket booking, and instant confirmation for a seamless and hassle-free journey.</p>
                </div>
            </div>

            <div class="col-md-4">
                <div class="info-card text-center">
                    <div class="info-icon-box mx-auto mb-4">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <h5 class="fw-bold mb-3">Safe & Reliable Travel</h5>
                    <p class="text-muted small">We ensure safe, smooth, and worry-free ferry journeys with trusted operators, professional service, and a reliable booking system designed for your comfort and convenience.</p>
                </div>
            </div>
        </div>
    </div>
</section> -->

<section class="group-trips-section py-5">
    <div class="container">
       <!-- <div class="row align-items-stretch mb-5">
            <div class="col-lg-5 mb-4 mb-lg-0">
                <div class="trip-img-card h-100">
                    <img src="corporate.webp" alt="Corporate Group" class="trip-img">
                </div>
            </div>
            <div class="col-lg-7">
                <div class="trip-text-card h-100 d-flex flex-column justify-content-center">
                    <h2 class="fw-bold mb-3" style="color: #0d1b2a;">Corporate Group Bookings</h2>
                    <p class="text-muted mb-4" style="font-size: 14px; line-height: 1.8;">
                        Simplify ferry arrangements for corporate teams with flexible schedules, competitive group pricing, and streamlined booking support, ensuring smooth travel planning for meetings, events, or company retreats to Batam.
                    </p>
                    <div>
                        <a href="https://wa.me/6582955180?text=Hi%20there!%20I%20have%20some%20questions%20on%20Batam%20Ferry%20Tickets!" target="_blank" class="btn btn-outline-route px-4 py-2">Learn More ↗</a>
                    </div>
                </div>
            </div>
        </div> -->

        <!-- <div class="row align-items-stretch flex-lg-row-reverse">
            <div class="col-lg-5 mb-4 mb-lg-0">
                <div class="trip-img-card h-100">
                    <img src="family.webp" alt="Family Trip" class="trip-img">
                </div>
            </div>
            <div class="col-lg-7">
                <div class="trip-text-card h-100 d-flex flex-column justify-content-center">
                    <h2 class="fw-bold mb-3" style="color: #0d1b2a;">Family & Leisure Trips</h2>
                    <p class="text-muted mb-4" style="font-size: 14px; line-height: 1.8;">
                        Enjoy a stress-free ferry booking experience designed for families, with convenient timings, comfortable travel options, and great value fares, making your Batam getaway relaxing, enjoyable, and easy to plan.
                    </p>
                    <div>
                        <a href="https://wa.me/6582955180?text=Hi%20there!%20I%20have%20some%20questions%20on%20Batam%20Ferry%20Tickets!" target="_blank" class="btn btn-outline-route px-4 py-2">Learn More ↗</a>
                    </div>
                </div>
            </div>
        </div> -->

       <!-- <div class="row align-items-stretch mb-5 pb-5 mt-5">
            <div class="col-lg-5 mb-4 mb-lg-0">
                <div class="trip-img-card h-100">
                    <img src="weekend.webp" alt="Harbour Terminal" class="trip-img">
                </div>
            </div>
            <div class="col-lg-7">
                <div class="trip-text-card h-100 d-flex flex-column justify-content-center">
                    <h2 class="fw-bold mb-3" style="color: #0d1b2a;">Weekend Getaways</h2>
                    <p class="text-muted mb-4" style="font-size: 14px; line-height: 1.8;">
                        Plan quick and hassle-free weekend trips to Batam with frequent ferry departures, easy booking options, and flexible schedules, perfect for short escapes, relaxation, and making the most of your time away.
                    </p>
                    <div>
                        <a href="https://wa.me/6582955180?text=Hi%20there!%20I%20have%20some%20questions%20on%20Batam%20Ferry%20Tickets!" target="_blank" class="btn btn-outline-route px-4 py-2">Learn More ↗</a>
                    </div>
                </div>
            </div>
        </div> -->

        <div class="row align-items-center mt-5">
            <div class="col-lg-6 mb-5 mb-lg-0">
                <div class="image-frame-wrapper">
                    <div class="tilted-frame">
                        <img src="licence.webp" alt="Majestic Ferry" class="main-frame-img">
                    </div>
                </div>
            </div>

            <div class="col-lg-6 ps-lg-5">
                <div class="text-start">
                    <span class="badge rounded-pill border text-muted px-3 py-2 mb-3" style="font-weight: 500; font-size: 11px; letter-spacing: 1px;">LICENSED</span>
                    <h2 class="fw-bold mb-4" style="color: #0d1b2a; font-size: 42px;">Trusted Ferry Booking Platform</h2>
                    
                    <p class="text-muted mb-4" style="font-size: 14px; line-height: 1.7;">
                        We partner with licensed and reliable ferry operators serving the Singapore to Bintan routes, ensuring a safe, smooth, and dependable travel experience for all passengers. Every booking is processed securely with instant confirmation, so you can plan your trip with confidence.
                    </p>
                    
                    <p class="text-muted mb-4" style="font-size: 14px; line-height: 1.7;">
                        With strong industry experience, we focus on providing accurate schedules, transparent pricing, and responsive customer support. Our platform is designed to make ferry ticket booking simple, convenient, and trustworthy for every traveler.
                    </p>

                    <a href="contact-us.php" class="btn btn-primary-red px-4 py-2 mt-2">Contact Us ↗</a>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="destinations-section py-5 bg-white">
    <div class="container">
        <div class="text-center mb-5">
            <span class="badge rounded-pill border text-muted px-3 py-2 mb-3" style="font-weight: 500; font-size: 11px; letter-spacing: 1px; text-transform: uppercase;">OUR POPULAR DESTINATIONS</span>
            <h2 class="fw-bold mb-4" style="color: #0d1b2a; font-size: 42px;">Popular Destinations</h2>
        </div>

        <div class="row g-4">
            <div class="col-lg-4 col-md-6">
                <div class="destination-card">
                    <div class="dest-img-wrapper">
                        <img src="destination1.webp" alt="Batam Centre">
                    </div>
                    <div class="dest-content">
                        <h4 class="fw-bold mb-2">Desaru</h4>
                        <p class="text-muted small mb-4">Enjoy a quick team bonding getaway in Desaru with the thrilling adventure water park and uforgettable moment.</p>
                        <a href="https://wa.me/6582955180?text=Hi%20there!%20I%20have%20some%20questions%20on%20Bintan%20Ferry%20Tickets!" target="_blank" class="btn btn-outline-route px-4 py-2">Learn More ↗</a>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="destination-card">
                    <div class="dest-img-wrapper">
                        <img src="destination2.webp" alt="Sekupang">
                    </div>
                    <div class="dest-content">
                        <h4 class="fw-bold mb-2">Bintan</h4>
                        <p class="text-muted small mb-4">Hold meetings and conferences at Bintan while giving your entire team some well-deserved relaxation and leisure away from work.</p>
                        <a href="https://wa.me/6582955180?text=Hi%20there!%20I%20have%20some%20questions%20on%20Bintan%20Ferry%20Tickets!" target="_blank" class="btn btn-outline-route px-4 py-2">Learn More ↗</a>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="destination-card">
                    <div class="dest-img-wrapper">
                        <img src="destination3.webp" alt="Nongsa">
                    </div>
                    <div class="dest-content">
                        <h4 class="fw-bold mb-2">Johor</h4>
                        <p class="text-muted small mb-4">Short getaway to Johor with customised itineraries combining meetings, team-building activities, durian tasting and shopping with us.</p>
                        <a href="https://wa.me/6582955180?text=Hi%20there!%20I%20have%20some%20questions%20on%20Bintan%20Ferry%20Tickets!" target="_blank" class="btn btn-outline-route px-4 py-2">Learn More ↗</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- <section class="clients-section py-5 bg-white">
    <div class="container">
        <div class="text-center mb-5">
            <span class="badge rounded-pill border text-muted px-3 py-2 mb-3" style="font-weight: 500; font-size: 11px; letter-spacing: 1px; text-transform: uppercase;">OUR CLIENTS</span>
            <h2 class="fw-bold mb-3" style="color: #0d1b2a; font-size: 42px;">Trusted By Travelers</h2>
            <p class="text-muted mx-auto" style="max-width: 600px; font-size: 15px;">Chosen by travelers for easy booking, dependable schedules, and a seamless ferry travel experience.</p>
        </div>

        <div class="row g-4 row-cols-2 row-cols-md-3 row-cols-lg-5 justify-content-center">
            <div class="col"><div class="client-logo-card"><img src="Google-Logo.webp" alt="Google"></div></div>
            <div class="col"><div class="client-logo-card"><img src="Mandiri-Logo.webp" alt="Microsoft"></div></div>
            <div class="col"><div class="client-logo-card"><img src="Ferrari-Logo.webp" alt="Shopee"></div></div>
            <div class="col"><div class="client-logo-card"><img src="Coca-cola-Logo.webp" alt="Lazada"></div></div>
            <div class="col"><div class="client-logo-card"><img src="Ricoh-Logo.webp" alt="DBS"></div></div>
            
            <div class="col"><div class="client-logo-card"><img src="ByteDance-Logo.webp" alt="OCBC"></div></div>
            <div class="col"><div class="client-logo-card"><img src="Fullerton-Health-Logo.webp" alt="Grab"></div></div>
            <div class="col"><div class="client-logo-card"><img src="Changi-Airport-Group-Logo.webp" alt="UOB"></div></div>
            <div class="col"><div class="client-logo-card"><img src="Capitaland-Logo.webp" alt="Singtel"></div></div>
            <div class="col"><div class="client-logo-card"><img src="National-University-of-Singapore-Logo.webp" alt="Resorts World"></div></div>

            <div class="col"><div class="client-logo-card"><img src="Singlife-Logo.webp" alt="Starhub"></div></div>
            <div class="col"><div class="client-logo-card"><img src="Cisco-Logo.webp" alt="MBS"></div></div>
            <div class="col"><div class="client-logo-card"><img src="Cycle-Carriage-Logo.webp" alt="CapitaLand"></div></div>
            <div class="col"><div class="client-logo-card"><img src="Lazada-Logo.webp" alt="Keppel"></div></div>
            <div class="col"><div class="client-logo-card"><img src="Total-Logo.webp" alt="Changi"></div></div>
        </div>
    </div>
</section> -->

<!-- <section class="reviews-section py-5 bg-white">
    <div class="container">
        <div class="text-center mb-5">
            <span class="badge rounded-pill border text-muted px-3 py-2 mb-3" style="font-weight: 500; font-size: 11px; letter-spacing: 1px; text-transform: uppercase;">OUR TESTIMONIALS</span>
            <h2 class="fw-bold mb-4" style="color: #0d1b2a; font-size: 42px;">Our Customer Reviews</h2>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-md-6">
                <div class="video-card large">
                    <div class="video-thumb-wrapper">
                        <iframe 
                    src="https://www.youtube.com/embed/TTDEZ-Riz34" 
                    title="YouTube video player" 
                    frameborder="0" 
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" 
                    allowfullscreen
                    style="width: 100%; aspect-ratio: 16/9;">
                </iframe>
                    </div>
                    <div class="video-info p-3">
                        <h5 class="fw-bold mb-0">Corporate Customer Testimonial - Batam</h5>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="video-card large">
                    <div class="video-thumb-wrapper">
                        <iframe 
                    src="https://www.youtube.com/embed/lKp2kA5TLD8" 
                    title="YouTube video player" 
                    frameborder="0" 
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" 
                    allowfullscreen
                    style="width: 100%; aspect-ratio: 16/9;">
                </iframe>
                    </div>
                    <div class="video-info p-3">
                        <h5 class="fw-bold mb-0">Liverpool vs Manchester United Match</h5>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-4 col-md-6">
                <div class="video-card small">
                    <div class="video-thumb-wrapper small-thumb">
                        <iframe 
                    src="https://www.youtube.com/embed/XOsE2At2yP4" 
                    title="YouTube video player" 
                    frameborder="0" 
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" 
                    allowfullscreen
                    style="width: 100%; aspect-ratio: 16/9;">
                </iframe>
                    </div>
                    <div class="video-info p-2 text-center">
                        <p class="fw-bold small mb-0">What customer say about VTL Travel and Tour Mount Bromo?</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="video-card small">
                    <div class="video-thumb-wrapper small-thumb">
                        <iframe 
                    src="https://www.youtube.com/embed/APjM_TaGfBU" 
                    title="YouTube video player" 
                    frameborder="0" 
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" 
                    allowfullscreen
                    style="width: 100%; aspect-ratio: 16/9;">
                </iframe>
                    </div>
                    <div class="video-info p-2 text-center">
                        <p class="fw-bold small mb-0">Japan Trip Customer Testimonial</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="video-card small">
                    <div class="video-thumb-wrapper small-thumb">
                       <iframe 
                    src="https://www.youtube.com/embed/_YoxpcHoir8" 
                    title="YouTube video player" 
                    frameborder="0" 
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" 
                    allowfullscreen
                    style="width: 100%; aspect-ratio: 16/9;">
                </iframe>
                    </div>
                    <div class="video-info p-2 text-center">
                        <p class="fw-bold small mb-0">Review from Midea Company for VTL & Bintan Team Building</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section> -->

<!-- <section class="clients-section py-5 bg-white">
    <div class="container">
        <div class="text-center mb-5">
            <span class="badge rounded-pill border text-muted px-3 py-2 mb-3" style="font-weight: 500; font-size: 11px; letter-spacing: 1px; text-transform: uppercase;">OUR PARTNERS</span>
            <h2 class="fw-bold mb-3" style="color: #0d1b2a; font-size: 42px;">Our Partners</h2>
            <p class="text-muted mx-auto" style="max-width: 600px; font-size: 15px;">We work with leading ferry operators to provide the best travel experience.</p>
        </div>

        <div class="row g-4 row-cols-2 row-cols-md-3 row-cols-lg-5 justify-content-center">
            <div class="col"><div class="client-logo-card"><img src="batam fast.png" alt="Google"></div></div>
            <div class="col"><div class="client-logo-card"><img src="Wonderful-Indonesia-Logo.webp" alt="Microsoft"></div></div>
            <div class="col"><div class="client-logo-card"><img src="Trip-Logo.webp" alt="Shopee"></div></div>
            <div class="col"><div class="client-logo-card"><img src="Ctrip-Logo.webp" alt="Lazada"></div></div>
            <div class="col"><div class="client-logo-card"><img src="Global-Tix-Logo.webp" alt="DBS"></div></div>
            
            <div class="col"><div class="client-logo-card"><img src="Viator-Logo.webp" alt="OCBC"></div></div>
            <div class="col"><div class="client-logo-card"><img src="Tripadvisor-Logo.webp" alt="Grab"></div></div>
            <div class="col"><div class="client-logo-card"><img src="Royal-Caribbean-Logo.webp" alt="UOB"></div></div>
            <div class="col"><div class="client-logo-card"><img src="Expedia-Logo.webp" alt="Singtel"></div></div>
            <div class="col"><div class="client-logo-card"><img src="G-Adventures-Logo.webp" alt="Resorts World"></div></div>

            <div class="col"><div class="client-logo-card"><img src="Traveloka-Logo.webp" alt="Starhub"></div></div>
            <div class="col"><div class="client-logo-card"><img src="Get-Your-Guide-Logo.webp" alt="MBS"></div></div>
            <div class="col"><div class="client-logo-card"><img src="changi-recommends-holidays-from-singapore.webp" alt="CapitaLand"></div></div>
            <div class="col"><div class="client-logo-card"><img src="Agoda-Logo.webp" alt="Keppel"></div></div>
            <div class="col"><div class="client-logo-card"><img src="klook-logo.webp" alt="Changi"></div></div>
        </div>
    </div>
</section> -->

<!-- <section class="travel-info-section py-5 bg-white">
    <div class="container">
        <div class="text-center mb-5">
            <span class="badge rounded-pill border text-muted px-3 py-2 mb-3" style="font-weight: 500; font-size: 11px; letter-spacing: 1px; text-transform: uppercase;">OUR BLOG</span>
            <h2 class="fw-bold mb-4" style="color: #0d1b2a; font-size: 42px;">Important Travel Information</h2>
        </div>

        <div class="row g-4">
            <div class="col-lg-4 col-md-6">
                <div class="blog-card">
                    <div class="blog-img-wrapper">
                        <img src="https://images.unsplash.com/photo-1544161513-0179fe746fd5?auto=format&fit=crop&q=80&w=600" alt="Ferry Travel Guide">
                    </div>
                    <div class="blog-content">
                        <div class="blog-date mb-2">
                            <i class="bi bi-calendar3 me-2"></i> April 3, 2026
                        </div>
                        <h4 class="fw-bold mb-3">Ferry Travel Guide to Batam</h4>
                        <p class="text-muted small mb-4">Learn everything about ferry travel, booking tips, boarding process, and what to expect during your journey.</p>
                        <a href="#" class="btn btn-primary-red px-4 py-2">Read More ↗</a>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="blog-card">
                    <div class="blog-img-wrapper">
                        <img src="https://images.unsplash.com/photo-1502781252888-9143ba7f074e?auto=format&fit=crop&q=80&w=600" alt="Visa & Entry Requirements">
                    </div>
                    <div class="blog-content">
                        <div class="blog-date mb-2">
                            <i class="bi bi-calendar3 me-2"></i> April 3, 2026
                        </div>
                        <h4 class="fw-bold mb-3">Visa & Entry Requirements</h4>
                        <p class="text-muted small mb-4">Understand visa rules, passport validity, and entry requirements for smooth and hassle-free travel to Batam.</p>
                        <a href="#" class="btn btn-outline-route px-4 py-2">Read More ↗</a>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="blog-card">
                    <div class="blog-img-wrapper">
                        <img src="https://images.unsplash.com/photo-1502781252888-9143ba7f074e?auto=format&fit=crop&q=80&w=600" alt="Ferry Schedule Tips">
                    </div>
                    <div class="blog-content">
                        <div class="blog-date mb-2">
                            <i class="bi bi-calendar3 me-2"></i> April 3, 2026
                        </div>
                        <h4 class="fw-bold mb-3">Ferry Schedule Tips</h4>
                        <p class="text-muted small mb-4">Get helpful tips on choosing ferry timings, avoiding peak hours, and planning your travel schedule efficiently.</p>
                        <a href="#" class="btn btn-outline-route px-4 py-2">Read More ↗</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section> -->

<!-- ========== CONNECT WITH US SECTION (WORKING FORM) ========== -->
    <section class="contact-section py-5 bg-white" id="journeyContactSection">
        <div class="container">
            <div class="row align-items-start">
                <div class="col-lg-5 mb-5 mb-lg-0">
                    <span class="badge rounded-pill border text-muted px-3 py-2 mb-3" style="font-weight: 500; font-size: 11px;">CONTACT US</span>
                    <h2 class="fw-bold mb-4" style="color: #0d1b2a; font-size: 42px;">Connect With Us To <br> Start Your Journey</h2>
                    <p class="text-muted mb-5" style="font-size: 14px;">Reach out to our team for assistance, bookings, or travel guidance and start planning your smooth Bintan ferry journey today.</p>
                    <div class="contact-methods mb-5"><h5 class="fw-bold mb-4">Our Contact:</h5>
                        <div class="d-flex align-items-center mb-4"><div class="contact-icon me-3"><i class="bi bi-telephone-fill"></i></div><div><div class="text-danger small fw-bold">Phone No:</div><div class="fw-bold text-dark">+65 8295 5180</div></div></div>
                        <div class="d-flex align-items-center mb-4"><div class="contact-icon me-3"><i class="bi bi-envelope-fill"></i></div><div><div class="text-danger small fw-bold">Email:</div><div class="fw-bold text-dark">hello@vtltravel.com</div></div></div>
                        <div class="d-flex align-items-center"><div class="contact-icon me-3"><i class="bi bi-geo-alt-fill"></i></div><div><div class="text-danger small fw-bold">Address:</div><div class="fw-bold text-dark">15 Beach Road, #02-01, Beach Centre, Singapore 189677</div></div></div>
                    </div>
                    <div class="follow-us"><h5 class="fw-bold mb-3">Follow Us:</h5><div class="social-icons d-flex gap-3"><a href="#"><i class="bi bi-tiktok text-danger fs-4"></i></a><a href="#"><i class="bi bi-facebook text-danger fs-4"></i></a><a href="#"><i class="bi bi-instagram text-danger fs-4"></i></a><a href="#"><i class="bi bi-linkedin text-danger fs-4"></i></a><a href="#"><i class="bi bi-pinterest text-danger fs-4"></i></a><a href="#"><i class="bi bi-youtube text-danger fs-4"></i></a></div></div>
                </div>
                <div class="col-lg-7 ps-lg-5">
                    <div class="contact-form-card shadow-sm">
                        <div id="journeyFormAlert"></div>
                        <form id="journeyConnectForm" method="POST">
                            <div class="mb-3"><label class="form-label small fw-bold">Full Name <span class="text-danger">*</span></label><input type="text" class="form-control" name="full_name" placeholder="Enter full name ..." required></div>
                            <div class="row mb-3"><div class="col-md-6"><label class="form-label small fw-bold">Email Address <span class="text-danger">*</span></label><input type="email" class="form-control" name="email" placeholder="Email address ..." required></div><div class="col-md-6"><label class="form-label small fw-bold">Phone Number</label><div class="input-group"><span class="input-group-text bg-white">SG +65</span><input type="text" class="form-control" name="phone" placeholder="XX XXX XXX"></div></div></div>
                            <div class="row mb-3"><div class="col-md-6"><label class="form-label small fw-bold">When you going? <span class="text-danger">*</span></label><input type="date" class="form-control" name="travel_date" required></div><div class="col-md-6"><label class="form-label small fw-bold">Number of Passengers <span class="text-danger">*</span></label><input type="number" class="form-control" name="passengers" placeholder="Enter number" min="1" required></div></div>
                            <div class="mb-3"><label class="form-label small fw-bold">What group is this? <span class="text-danger">*</span></label><select class="form-select" name="route" required><option value="" disabled selected>Select a Group</option>
                                        <option value="Corporate Group">Corporate Group</option>
                                        <option value="Community Centre">Community Centre</option>
                                        <option value="Church Retreat">Church Retreat</option>
                                        <option value="Family">Family</option>
                                        <option value="Group of Friends">Group of Friends</option>
                                        <option value="Others">Others</option>
                            </select></div>
                            <div class="mb-4"><label class="form-label small fw-bold">Message</label><textarea class="form-control" name="message" rows="4" placeholder="Tell us about your travel plans or questions."></textarea></div>
                            <button type="submit" class="btn btn-primary-red w-100 py-3" id="journeySubmitBtn">Send Message ↗</button>
                        </form>
                    </div>
                </div>
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
        // Set up the date input to act as a placeholder for a date picker
        document.getElementById('travel_date').addEventListener('focus', function() {
            this.type = 'date';
        });
        document.getElementById('travel_date').addEventListener('blur', function() {
            if (this.value === "") {
                this.type = 'text';
            }
        });
    </script>
<script>			var url = 'https://prod-crmb2b.s3.ap-southeast-1.amazonaws.com/widget/64c917df6f3bf83bd6d2b5b3/69954aba97bf000e7bb2baf4.js?v=' + Date.now();			var s = document.createElement('script');			s.type = 'text/javascript';			s.async = true;			s.src = url;			s.onload = function() {				CreateWhatsappChatWidget();			};			var x = document.getElementsByTagName('script')[0];			x.parentNode.insertBefore(s, x);		</script>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</body>
</html>