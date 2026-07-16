<?= $this->extend('layouts/landing') ?>

<?= $this->section('title') ?>SIMENAK Z'Plack<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
    #hero-landing {
        overflow-x: clip;
        max-width: 100%;
    }

    #hero-landing .hero-grid {
        align-items: center;
        max-width: 100%;
    }

    #hero-landing .hero-copy {
        position: relative;
        z-index: 2;
        min-width: 0;
    }

    #hero-landing .hero-visual-col {
        min-width: 0;
        max-width: 100%;
    }

    #hero-landing .hero-illustration-frame {
        width: 100%;
        max-width: 100%;
        box-sizing: border-box;
        padding: 0 2rem 0 0.5rem;
        overflow: visible;
    }

    #hero-landing .hero-illustration {
        display: block;
        width: 100% !important;
        max-width: 100% !important;
        height: auto !important;
        object-fit: contain;
        object-position: center center;
    }

    @media (max-width: 639px) {
        #hero-landing .hero-illustration {
            max-height: 300px;
        }
    }

    @media (min-width: 640px) and (max-width: 1023px) {
        #hero-landing .hero-illustration {
            max-height: 400px;
        }
    }

    @media (min-width: 1024px) {
        #hero-landing .hero-grid {
            display: grid;
            grid-template-columns: minmax(0, 30fr) minmax(0, 70fr);
            gap: 2.5rem;
            width: 100%;
            max-width: 100%;
            align-items: center;
        }

        #hero-landing .hero-copy {
            max-width: none;
        }

        #hero-landing .hero-visual-col {
            max-width: none;
        }

        #hero-landing .hero-illustration {
            max-height: 38rem;
        }
    }

    @media (min-width: 1280px) {
        #hero-landing .hero-grid {
            gap: 3rem;
        }

        #hero-landing .hero-illustration {
            max-height: 42rem;
        }
    }

    @media (min-width: 1536px) {
        #hero-landing .hero-illustration {
            max-height: 44rem;
        }
    }

    #about .about-badge {
        display: inline-block;
        padding: 6px 18px;
        border-radius: 999px;
        border: 1px solid rgba(46, 92, 230, 0.35);
        color: #2E5CE6;
        font-size: 12px;
        font-weight: 600;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    #about .about-heading {
        color: #051747;
        font-size: clamp(1.75rem, 3.2vw, 2.5rem);
        font-weight: 800;
        line-height: 1.25;
    }

    #about .about-feature-card {
        background: #fff;
        border: 1px solid #E2E8F0;
        border-radius: 14px;
        padding: 1.5rem;
        transition: background .3s ease, border-color .3s ease, box-shadow .3s ease, color .3s ease;
    }

    #about .about-feature-card:hover {
        background: #2E5CE6;
        border-color: #2E5CE6;
        box-shadow: 0 14px 36px rgba(46, 92, 230, 0.28);
    }

    #about .about-feature-card .about-card-icon {
        color: #2E5CE6;
        transition: color .3s ease;
    }

    #about .about-feature-card .about-card-title {
        color: #051747;
        transition: color .3s ease;
    }

    #about .about-feature-card .about-card-desc {
        color: #64748b;
        transition: color .3s ease;
    }

    #about .about-feature-card .about-card-link {
        color: #2E5CE6;
        transition: color .3s ease;
    }

    #about .about-feature-card:hover .about-card-icon,
    #about .about-feature-card:hover .about-card-title,
    #about .about-feature-card:hover .about-card-desc,
    #about .about-feature-card:hover .about-card-link {
        color: #fff;
    }

    #about .about-cards-stagger {
        display: grid;
        grid-template-columns: 1fr;
        gap: 1rem;
    }

    @media (min-width: 768px) {
        #about .about-cards-stagger {
            grid-template-columns: 1fr 1fr;
            align-items: center;
            gap: 1.25rem;
        }

        #about .about-cards-col-left {
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }
    }

    #services .services-heading {
        color: #051747;
        font-size: clamp(1.75rem, 3.5vw, 2.75rem);
        font-weight: 800;
        line-height: 1.2;
    }

    #services .service-ref-card {
        background: #fff;
        border-radius: 22px;
        padding: 2rem 1.75rem;
        min-height: 280px;
        box-shadow: 0 14px 44px rgba(15, 23, 42, 0.14), 0 6px 18px rgba(15, 23, 42, 0.08);
        transition: background .3s ease, box-shadow .3s ease, color .3s ease;
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        min-height: 100%;
        position: relative;
        z-index: 1;
    }

    #services .service-ref-card:hover {
        background: #051747;
        box-shadow: 0 18px 48px rgba(5, 23, 71, 0.28);
    }

    #services .service-ref-card-title {
        color: #051747;
        font-size: 1.35rem;
        font-weight: 800;
        line-height: 1.3;
        transition: color .3s ease;
    }

    #services .service-ref-card-desc {
        color: #64748b;
        font-size: 0.875rem;
        line-height: 1.7;
        margin-top: 0.25rem;
        flex-grow: 1;
        transition: color .3s ease;
    }

    #services .service-ref-card:hover .service-ref-card-title,
    #services .service-ref-card:hover .service-ref-card-desc {
        color: #fff;
    }

    #services .services-blur-mesh {
        pointer-events: none;
        top: -10rem;
        bottom: -10rem;
    }

    #services .services-blur-blob {
        position: absolute;
        border-radius: 9999px;
        opacity: 0.4;
        aspect-ratio: 1 / 1;
    }

    #services .service-icon-blob {
        width: 72px;
        height: 72px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 1.25rem 0 1rem;
        flex-shrink: 0;
        border-radius: 58% 42% 45% 55% / 52% 48% 52% 48%;
    }

    #services .service-icon-blob svg {
        width: 28px;
        height: 28px;
    }

    #services .service-icon-blob--blue {
        background: #2563eb;
        color: #fff;
    }

    #services .service-icon-blob--pink {
        background: #fce7f3;
        color: #db2777;
    }

    #services .service-icon-blob--peach {
        background: #ffedd5;
        color: #ea580c;
    }

    #services .service-icon-blob--violet {
        background: #ede9fe;
        color: #7c3aed;
    }

    #process .process-heading {
        color: #051747;
        font-size: clamp(1.75rem, 3.2vw, 2.5rem);
        font-weight: 800;
        line-height: 1.25;
    }

    #process .process-flow {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 1.25rem;
    }

    @media (min-width: 1280px) {
        #process .process-flow {
            flex-direction: row;
            align-items: stretch;
            justify-content: center;
            gap: 0.5rem;
        }
    }

    #process .process-step-card {
        background: #fff;
        border-radius: 20px;
        padding: 1.5rem;
        box-shadow: 0 4px 18px rgba(15, 23, 42, 0.09);
        text-align: left;
        width: 100%;
        max-width: 280px;
    }

    @media (min-width: 1280px) {
        #process .process-step-card {
            flex: 1 1 0;
            min-width: 0;
            max-width: none;
        }
    }

    #process .process-step-icon-wrap {
        width: 72px;
        height: 72px;
        border-radius: 58% 42% 45% 55% / 52% 48% 52% 48%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        margin-top: 1rem;
    }

    #process .process-step-icon-wrap svg {
        width: 28px;
        height: 28px;
        stroke: currentColor;
        fill: none;
    }

    #process .process-step-head {
        display: flex;
        align-items: center;
        gap: 0.625rem;
        margin-top: 0;
    }

    #process .process-step-num {
        width: 28px;
        height: 28px;
        border-radius: 9999px;
        background: #051747;
        color: #fff;
        font-size: 0.8125rem;
        font-weight: 700;
        line-height: 1;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    #process .process-step-title {
        margin-top: 0;
        color: #051747;
        font-size: 1rem;
        font-weight: 800;
        line-height: 1.35;
    }

    #process .process-step-desc {
        margin-top: 0.75rem;
        color: #64748b;
        font-size: 0.8125rem;
        line-height: 1.65;
    }

    #process .process-connector {
        display: none;
        flex-shrink: 0;
        align-self: center;
        margin-top: 0;
        color: #2E5CE6;
    }

    #process .process-connector svg {
        width: 4.25rem;
        height: auto;
    }

    @media (min-width: 1280px) {
        #process .process-connector {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 4.25rem;
        }
    }

    #process .process-connector-mobile {
        display: flex;
        color: #2E5CE6;
    }

    #process .process-connector-mobile svg {
        width: auto;
        height: 4.25rem;
    }

    @media (min-width: 1280px) {
        #process .process-connector-mobile {
            display: none;
        }
    }

    #process .process-step-icon-wrap--blue {
        background: rgba(46, 92, 230, 0.12);
        color: #2E5CE6;
    }

    #process .process-step-icon-wrap--indigo {
        background: rgba(99, 102, 241, 0.12);
        color: #6366f1;
    }

    #process .process-step-icon-wrap--cyan {
        background: rgba(14, 165, 233, 0.12);
        color: #0ea5e9;
    }

    #process .process-step-icon-wrap--navy {
        background: rgba(5, 23, 71, 0.1);
        color: #051747;
    }

    #process .process-step-icon-wrap--teal {
        background: rgba(20, 184, 166, 0.12);
        color: #14b8a6;
    }

    #faq .faq-heading {
        color: #051747;
        font-size: clamp(1.75rem, 3.2vw, 2.5rem);
        font-weight: 800;
        line-height: 1.25;
    }

    #faq .faq-list {
        margin-top: 2.5rem;
        text-align: left;
        border-top: 1px solid #e2e8f0;
    }

    #faq .faq-item {
        border-bottom: 1px solid #e2e8f0;
    }

    #faq .faq-question {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1.25rem 0;
        font-size: 0.9375rem;
        font-weight: 700;
        color: #051747;
        cursor: pointer;
        list-style: none;
    }

    #faq .faq-question::-webkit-details-marker {
        display: none;
    }

    #faq .faq-chevron {
        flex-shrink: 0;
        width: 1.25rem;
        height: 1.25rem;
        color: #64748b;
        transition: transform 0.2s ease;
    }

    #faq .faq-item[open] .faq-chevron {
        transform: rotate(180deg);
    }

    #faq .faq-answer {
        padding: 0 0 1.25rem;
        color: #64748b;
        font-size: 0.875rem;
        line-height: 1.7;
    }

    #portfolio .portfolio-heading {
        color: #051747;
        font-size: clamp(1.75rem, 3.2vw, 2.5rem);
        font-weight: 800;
        line-height: 1.25;
    }

    #portfolio .project-slider__viewport {
        overflow: hidden;
        margin-top: 2.5rem;
    }

    #portfolio .project-slider__track {
        display: flex;
        will-change: transform;
        transition: transform 0.55s cubic-bezier(0.4, 0, 0.2, 1);
    }

    #portfolio .project-card {
        flex: 0 0 100%;
        padding: 0 0.625rem;
        box-sizing: border-box;
    }

    @media (min-width: 640px) {
        #portfolio .project-card {
            flex: 0 0 50%;
        }
    }

    @media (min-width: 1024px) {
        #portfolio .project-card {
            flex: 0 0 33.3333%;
        }
    }

    #portfolio .project-card__shell {
        position: relative;
        padding: 0 0 1.25rem 0;
    }

    #portfolio .project-card__accent {
        position: absolute;
        left: 0.75rem;
        right: -0.75rem;
        bottom: 0;
        height: 0;
        background: #2E5CE6;
        border-radius: 6px;
        transition: height 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        z-index: 0;
    }

    #portfolio .project-card:hover .project-card__accent,
    #portfolio .project-card.is-active .project-card__accent {
        height: 42%;
    }

    #portfolio .project-card__body {
        position: relative;
        z-index: 1;
        background: #fff;
        border-radius: 14px;
        overflow: hidden;
        box-shadow: 0 8px 28px rgba(15, 23, 42, 0.08);
        transition: transform 0.45s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.45s ease;
    }

    #portfolio .project-card:hover .project-card__body,
    #portfolio .project-card.is-active .project-card__body {
        transform: translateY(-8px);
        box-shadow: 0 16px 40px rgba(46, 92, 230, 0.18);
    }

    #portfolio .project-card__media {
        position: relative;
        overflow: hidden;
        aspect-ratio: 4 / 3;
        background: #e2e8f0;
    }

    #portfolio .project-card__media img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
        transition: transform 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        will-change: transform;
    }

    #portfolio .project-card__overlay {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(255, 255, 255, 0.72);
        opacity: 0;
        transition: opacity 0.35s ease;
    }

    #portfolio .project-card:hover .project-card__overlay,
    #portfolio .project-card.is-active .project-card__overlay {
        opacity: 1;
    }

    #portfolio .project-card__link-icon {
        width: 2.75rem;
        height: 2.75rem;
        border-radius: 9999px;
        background: #2E5CE6;
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 8px 20px rgba(46, 92, 230, 0.35);
    }

    #portfolio .project-card__title {
        position: relative;
        z-index: 2;
        margin: 0;
        padding: 0.875rem 1.125rem 0.625rem;
        font-size: 1.0625rem;
        font-weight: 800;
        color: #051747;
        transition: color 0.35s ease;
        pointer-events: none;
    }

    #portfolio .project-card:hover .project-card__title,
    #portfolio .project-card.is-active .project-card__title {
        color: #fff;
    }

    #portfolio .project-slider__nav {
        display: flex;
        justify-content: center;
        gap: 0.75rem;
        margin-top: 2rem;
    }

    #portfolio .project-slider__btn {
        width: 2.5rem;
        height: 2.5rem;
        border-radius: 9999px;
        border: 1px solid #dbeafe;
        background: #eff6ff;
        color: #051747;
        font-size: 1.125rem;
        font-weight: 700;
        line-height: 1;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: background 0.2s ease, color 0.2s ease, border-color 0.2s ease;
    }

    #portfolio .project-slider__btn:hover:not(:disabled) {
        background: #2E5CE6;
        border-color: #2E5CE6;
        color: #fff;
    }

    #portfolio .project-slider__btn:disabled {
        opacity: 0.45;
        cursor: not-allowed;
    }

    #catalog .catalog-badge {
        display: inline-block;
        padding: 6px 18px;
        border-radius: 999px;
        border: 1px solid rgba(46, 92, 230, 0.35);
        color: #2E5CE6;
        font-size: 12px;
        font-weight: 600;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    #catalog .catalog-heading {
        color: #051747;
        font-size: clamp(1.75rem, 3.2vw, 2.5rem);
        font-weight: 800;
        line-height: 1.25;
    }

    #catalog .catalog-tab-scroll {
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
    }

    #catalog .catalog-tab-scroll::-webkit-scrollbar {
        display: none;
    }

    #catalog .catalog-tab {
        flex-shrink: 0;
        white-space: nowrap;
    }

    #catalog .katalog-card {
        background: #fff;
        border: 1px solid #E8EDF3;
        border-radius: 16px;
        box-shadow: 0 2px 12px rgba(15, 23, 42, 0.06);
        transition: transform .22s ease, box-shadow .22s ease;
        overflow: hidden;
    }

    #catalog .katalog-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 28px rgba(15, 23, 42, 0.1);
    }

    #catalog .katalog-card-media {
        position: relative;
        aspect-ratio: 4 / 3;
        background: #F1F5F9;
        overflow: hidden;
    }

    #catalog .katalog-card-badge {
        position: absolute;
        top: 0.625rem;
        left: 0.625rem;
        z-index: 2;
        padding: 0.25rem 0.625rem;
        border-radius: 9999px;
        font-size: 0.6875rem;
        font-weight: 600;
        line-height: 1.2;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.06);
    }

    #catalog .katalog-card-media img {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center;
        pointer-events: none;
    }

    #catalog .katalog-card-image-zoom {
        position: absolute;
        inset: 0;
        z-index: 1;
        margin: 0;
        padding: 0;
        border: 0;
        background: transparent;
        cursor: zoom-in;
    }

    #catalog .katalog-card-image-zoom:focus-visible {
        outline: 2px solid #2E5CE6;
        outline-offset: -2px;
    }

    #catalog .katalog-card-placeholder {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #F1F5F9;
    }

    #catalog .katalog-min-order-tag {
        display: inline-block;
        margin-top: 0.5rem;
        padding: 0.25rem 0.5rem;
        border-radius: 0.375rem;
        background: #F1F5F9;
        font-size: 0.6875rem;
        font-weight: 500;
        color: #475569;
        line-height: 1.3;
    }

    #catalog #catalogSearchBar {
        border-color: #CBD5E1;
        transition: border-color .2s, box-shadow .2s;
        box-shadow: 0 1px 4px rgba(15, 23, 42, 0.04);
    }

    #catalog #catalogSearchBar.focused {
        border-color: #2E5CE6 !important;
        box-shadow: 0 0 0 3px rgba(46, 92, 230, 0.12);
    }

    #catalogImageLightbox {
        position: fixed;
        inset: 0;
        z-index: 100;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 1rem;
        background: rgba(15, 23, 42, 0.82);
    }

    #catalogImageLightbox.is-open {
        display: flex;
    }

    #catalogImageLightbox img {
        max-width: min(100%, 56rem);
        max-height: 90vh;
        width: auto;
        height: auto;
        object-fit: contain;
        border-radius: 0.5rem;
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.35);
    }

    #catalogImageLightboxClose {
        position: absolute;
        top: 1rem;
        right: 1rem;
        z-index: 2;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 2.5rem;
        height: 2.5rem;
        border: 0;
        border-radius: 9999px;
        background: rgba(255, 255, 255, 0.95);
        color: #051747;
        font-size: 1.5rem;
        line-height: 1;
        cursor: pointer;
    }

    #catalogImageLightboxCaption {
        position: absolute;
        bottom: 1.25rem;
        left: 50%;
        transform: translateX(-50%);
        max-width: 90%;
        padding: 0.375rem 0.75rem;
        border-radius: 9999px;
        background: rgba(255, 255, 255, 0.92);
        color: #051747;
        font-size: 0.75rem;
        font-weight: 600;
        text-align: center;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .premium-cta-card {
        position: relative;
        border: none !important;
        background: linear-gradient(128deg, #051747 0%, #0c2d6e 38%, #1e40af 62%, #2E5CE6 100%);
        box-shadow: 0 16px 48px rgba(5, 23, 71, 0.24), 0 10px 32px rgba(46, 92, 230, 0.22);
        overflow: hidden;
    }

    .premium-cta-card__mesh {
        pointer-events: none;
    }

    .premium-cta-card__blob {
        position: absolute;
        border-radius: 9999px;
        aspect-ratio: 1 / 1;
    }

    .premium-cta-card__blob--indigo {
        top: -28%;
        right: -6%;
        width: 14rem;
        height: 14rem;
        background: linear-gradient(to bottom right, #4338ca, #a855f7);
        opacity: 0.68;
        filter: blur(52px);
    }

    .premium-cta-card__blob--cyan {
        bottom: -32%;
        left: -4%;
        width: 12rem;
        height: 12rem;
        background: linear-gradient(to right, #06b6d4, #38bdf8);
        opacity: 0.58;
        filter: blur(48px);
    }

    .premium-cta-card__blob--blue {
        top: 18%;
        left: 32%;
        width: 10rem;
        height: 10rem;
        background: linear-gradient(to bottom right, #2563eb, #2E5CE6);
        opacity: 0.55;
        filter: blur(44px);
    }

    @media (prefers-reduced-motion: no-preference) {
        .scroll-reveal {
            opacity: 0;
            transition:
                opacity var(--reveal-duration, 0.65s) cubic-bezier(0.22, 1, 0.36, 1),
                transform var(--reveal-duration, 0.65s) cubic-bezier(0.22, 1, 0.36, 1);
            transition-delay: var(--reveal-delay, 0ms);
            will-change: opacity, transform;
        }

        .scroll-reveal--up {
            transform: translate3d(0, var(--reveal-distance, 24px), 0);
        }

        .scroll-reveal--left {
            transform: translate3d(calc(var(--reveal-distance, 24px) * -1), 0, 0);
        }

        .scroll-reveal--right {
            transform: translate3d(var(--reveal-distance, 24px), 0, 0);
        }

        .scroll-reveal--visible {
            opacity: 1;
            transform: translate3d(0, 0, 0);
        }
    }

    @media (prefers-reduced-motion: no-preference) and (max-width: 767px) {
        .scroll-reveal {
            --reveal-duration: 0.55s;
            --reveal-distance: 18px;
        }
    }

    @media (prefers-reduced-motion: no-preference) and (min-width: 768px) {
        .scroll-reveal {
            --reveal-duration: 0.75s;
            --reveal-distance: 32px;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .scroll-reveal {
            opacity: 1 !important;
            transform: none !important;
            transition: none !important;
        }
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
$isLoggedIn = (bool) session()->get('isLoggedIn');
$katalogAktif = isset($katalogAktif) && is_array($katalogAktif) ? $katalogAktif : [];
$tabLabels = [
    'all' => 'Semua',
    'desain_grafis' => 'Desain',
    'cetak_digital' => 'Digital',
    'cetak_offset' => 'Offset',
    'media_promosi' => 'Promosi',
];
$kategoriLabelMap = [
    'desain_grafis' => 'Desain Grafis',
    'cetak_digital' => 'Cetak Digital',
    'cetak_offset'  => 'Cetak Offset',
    'media_promosi' => 'Media Promosi',
];
$kategoriTabShort = [
    'desain_grafis' => 'Desain',
    'cetak_digital' => 'Digital',
    'cetak_offset'  => 'Offset',
    'media_promosi' => 'Promosi',
];
$kategoriBadgeStyle = [
    'desain_grafis' => ['bg' => '#F3E8FF', 'text' => '#6B21A8'],
    'cetak_digital' => ['bg' => '#DBEAFE', 'text' => '#1E40AF'],
    'cetak_offset'  => ['bg' => '#FFEDD5', 'text' => '#9A3412'],
    'media_promosi' => ['bg' => '#DCFCE7', 'text' => '#166534'],
];
$kategoriBadgeStyleDefault = ['bg' => '#F1F5F9', 'text' => '#475569'];
$heroFiles = [
    '1.png',
    'illustration2.png',
    'illustration2.svg',
    'illustration.png',
];
$heroUrl = null;
foreach ($heroFiles as $heroFile) {
    if (is_file(FCPATH . 'assets/' . $heroFile)) {
        $heroUrl = base_url('assets/' . $heroFile);
        break;
    }
}
$pesanSekarangHref = $isLoggedIn ? site_url('katalog') : '#';
?>

<!-- HERO SECTION (gradient blur mesh — referensi Astrolus HeroSection) -->
<section id="hero-landing" class="relative overflow-x-clip bg-transparent pt-24 pb-12 md:pb-16">
    <div aria-hidden="true" class="pointer-events-none absolute -top-24 left-0 right-0 bottom-0 grid grid-cols-2 -space-x-52 opacity-40">
        <div class="blur-[106px] h-56 bg-gradient-to-br from-indigo-600 to-purple-400"></div>
        <div class="blur-[106px] h-32 bg-gradient-to-r from-cyan-400 to-sky-300"></div>
    </div>

    <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="hero-grid grid gap-8 lg:gap-10 items-center">
            <div class="hero-copy">
                <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white text-[10px] uppercase tracking-[0.2em] text-[#2E5CE6] font-bold shadow-sm border border-slate-100">Cetak Presisi Tinggi Selaras Kebutuhan</span>
                <h1 class="mt-3 md:mt-4 text-4xl md:text-6xl leading-tight font-extrabold text-[#051747]">
                    Layanan Cetak
                    <span class="block bg-gradient-to-r from-blue-950 via-blue-900 to-indigo-950 bg-clip-text text-transparent">Profesional &amp; Estetik.</span>
                </h1>
                <p class="mt-4 text-sm md:text-base text-slate-600 leading-relaxed max-w-lg">
                    Cetak presisi, pesanan terpantau. Pesan cetak digital, offset, hingga
                    undangan custom. Semua tercatat rapi lewat <strong>SIMENAK</strong>,
                    dari order sampai produk jadi.
                </p>
                <div class="flex flex-row items-center gap-3 mt-6">
                    <a href="<?= esc($pesanSekarangHref) ?>" class="btn-primary px-6 py-3 text-xs whitespace-nowrap" <?= $isLoggedIn ? '' : ' data-open-modal="loginModal"' ?>>Pesan Sekarang</a>
                    <a href="#services" class="btn-outline px-6 py-3 text-xs whitespace-nowrap">Pelajari Layanan</a>
                </div>
            </div>

            <div class="hero-visual-col">
                <div class="hero-illustration-frame">
                    <img src="<?= esc($heroUrl ?? base_url('assets/1.png')) ?>"
                        alt="Ilustrasi Mesin Cetak Z'Plack"
                        class="hero-illustration"
                        loading="eager">
                </div>
            </div>
        </div>
    </div>
</section>
<!-- END HERO SECTION -->

<section id="about" class="relative z-10 py-16 md:py-20 bg-transparent">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid lg:grid-cols-2 gap-12 lg:gap-16 items-center">
            <!-- KIRI: TEKS -->
            <div class="max-w-xl">
                <span class="about-badge">SIAPA KAMI</span>
                <h2 class="about-heading mt-5">Mitra Cetak Terpercaya Sejak 2010.</h2>
                <p class="mt-5 text-[14px] text-[#64748b] leading-relaxed">
                    Z'Plack melayani percetakan digital, offset, desain grafis, dan
                    media promosi dalam satu platform pesanan yang terstruktur.
                    Setiap pesanan dikelola dari awal hingga selesai—terpusat,
                    terdokumentasi, dan dapat dipantau secara langsung oleh pelanggan.
                </p>
                <a href="#services" class="inline-flex items-center justify-center mt-8 px-7 py-3 rounded-lg bg-[#2E5CE6] text-white text-sm font-semibold hover:bg-[#051747] transition-colors">
                    Jelajahi Lebih Lanjut
                </a>
            </div>

            <!-- KANAN: STAGGERED CARDS -->
            <div class="about-cards-stagger">
                <div class="about-cards-col-left">
                    <article class="about-feature-card">
                        <svg class="about-card-icon h-6 w-6 mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                        <h3 class="about-card-title text-lg font-bold mb-2">Pesanan Terpusat</h3>
                        <p class="about-card-desc text-sm leading-relaxed mb-4">
                            Semua pesanan masuk dalam satu platform, tidak lagi tersebar di WhatsApp atau email.
                        </p>
                        <a href="#services" class="about-card-link inline-flex items-center gap-1 text-sm font-semibold">
                            Selengkapnya <?= view('partials/order_detail_svg_icon', ['icon' => 'arrow-right', 'class' => 'h-3.5 w-3.5 shrink-0']) ?>
                        </a>
                    </article>

                    <article class="about-feature-card">
                        <svg class="about-card-icon h-6 w-6 mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                        <h3 class="about-card-title text-lg font-bold mb-2">Pelacakan Status Real-Time</h3>
                        <p class="about-card-desc text-sm leading-relaxed mb-4">
                            Pelanggan dapat memantau progres pesanan kapan saja tanpa perlu menghubungi admin.
                        </p>
                        <a href="#services" class="about-card-link inline-flex items-center gap-1 text-sm font-semibold">
                            Selengkapnya <?= view('partials/order_detail_svg_icon', ['icon' => 'arrow-right', 'class' => 'h-3.5 w-3.5 shrink-0']) ?>
                        </a>
                    </article>
                </div>

                <article class="about-feature-card">
                    <svg class="about-card-icon h-6 w-6 mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    <h3 class="about-card-title text-lg font-bold mb-2">Revisi Desain Terstruktur</h3>
                    <p class="about-card-desc text-sm leading-relaxed mb-4">
                        Proses revisi dikelola dengan approval history dan kuota yang jelas per pesanan.
                    </p>
                    <a href="#services" class="about-card-link inline-flex items-center gap-1 text-sm font-semibold">
                        Selengkapnya <?= view('partials/order_detail_svg_icon', ['icon' => 'arrow-right', 'class' => 'h-3.5 w-3.5 shrink-0']) ?>
                    </a>
                </article>
            </div>
        </div>
    </div>
</section>

<section id="services" class="relative z-0 overflow-visible bg-transparent py-16 md:py-20">
    <div aria-hidden="true" class="services-blur-mesh absolute left-0 right-0 overflow-visible">
        <div class="services-blur-blob -top-12 left-[18%] w-24 h-24 bg-gradient-to-br from-indigo-600 to-purple-400 blur-[64px]"></div>
        <div class="services-blur-blob top-20 left-[6%] w-28 h-28 bg-gradient-to-br from-indigo-600 to-purple-400 blur-[72px]"></div>
        <div class="services-blur-blob top-32 left-1/2 -translate-x-1/2 w-24 h-24 bg-gradient-to-r from-cyan-400 to-sky-300 blur-[64px]"></div>
        <div class="services-blur-blob bottom-24 right-[8%] w-32 h-32 bg-gradient-to-br from-indigo-600 to-sky-300 blur-[72px]"></div>
        <div class="services-blur-blob -bottom-10 right-[22%] w-24 h-24 bg-gradient-to-r from-cyan-400 to-sky-300 blur-[64px]"></div>
    </div>

    <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12 md:mb-14">
            <p class="text-xs uppercase tracking-[0.2em] text-[#2E5CE6] font-bold mb-3">Layanan Kami</p>
            <h2 class="services-heading">Cetak untuk Bisnis &amp; Kebutuhan Pribadi.</h2>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-6 lg:gap-8">
            <article class="service-ref-card">
                <h3 class="service-ref-card-title">Undangan &amp; Kartu Cetak.</h3>
                <div class="service-icon-blob service-icon-blob--blue" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                </div>
                <p class="service-ref-card-desc">
                    Cetak undangan pernikahan, khitanan, dan kartu nama dengan pilihan material dan finishing sesuai kebutuhan.
                </p>
            </article>

            <article class="service-ref-card">
                <h3 class="service-ref-card-title">Cetak Offset.</h3>
                <div class="service-icon-blob service-icon-blob--pink" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div>
                <p class="service-ref-card-desc">
                    Produksi massal untuk brosur, map, kop surat, kalender, dan kebutuhan cetak korporat dengan hasil yang konsisten.
                </p>
            </article>

            <article class="service-ref-card">
                <h3 class="service-ref-card-title">Cetak Digital.</h3>
                <div class="service-icon-blob service-icon-blob--peach" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" />
                    </svg>
                </div>
                <p class="service-ref-card-desc">
                    Stiker, poster, banner, dan media promosi dengan kualitas detail tinggi serta proses pengerjaan yang cepat.
                </p>
            </article>

            <article class="service-ref-card">
                <h3 class="service-ref-card-title">Desain Grafis &amp; Media Promosi.</h3>
                <div class="service-icon-blob service-icon-blob--violet" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                    </svg>
                </div>
                <p class="service-ref-card-desc">
                    Layanan desain dan produksi media promosi yang disesuaikan dengan identitas visual dan kebutuhan bisnis.
                </p>
            </article>
        </div>
    </div>
</section>

<section id="process" class="relative z-10 bg-transparent px-4 py-16 sm:px-6 md:px-8 md:py-20">
    <div class="max-w-7xl mx-auto">
        <div class="text-center mb-12 md:mb-14">
            <h2 class="process-heading">Cara Kerja SIMENAK</h2>
            <p class="mt-3 text-sm md:text-base text-[#64748b] leading-relaxed max-w-2xl mx-auto">
                Lima langkah terstruktur dari pemesanan hingga pesanan di tangan.
            </p>
        </div>

        <div class="process-flow">
            <article class="process-step-card">
                <div class="process-step-head">
                    <span class="process-step-num">1</span>
                    <h3 class="process-step-title">Pemesanan</h3>
                </div>
                <div class="process-step-icon-wrap process-step-icon-wrap--blue" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2" />
                        <path d="M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        <path d="M9 12h6M9 16h4" />
                    </svg>
                </div>
                <p class="process-step-desc">Pilih produk dari katalog, lengkapi spesifikasi pesanan, unggah referensi desain, tentukan deadline, lalu lakukan pembayaran DP 50% untuk mengonfirmasi pesanan.</p>
            </article>

            <div class="process-connector-mobile" aria-hidden="true">
                <svg viewBox="0 0 12 52" fill="none">
                    <circle cx="6" cy="6" r="4.5" stroke="currentColor" stroke-width="1.5" />
                    <path d="M6 12v30" stroke="currentColor" stroke-width="2" stroke-dasharray="4 3" stroke-linecap="round" />
                    <path d="M2.5 44.5L6 49.5L9.5 44.5" fill="currentColor" />
                </svg>
            </div>
            <div class="process-connector" aria-hidden="true">
                <svg viewBox="0 0 52 12" fill="none">
                    <circle cx="6" cy="6" r="4.5" stroke="currentColor" stroke-width="1.5" />
                    <path d="M12 6h30" stroke="currentColor" stroke-width="2" stroke-dasharray="4 3" stroke-linecap="round" />
                    <path d="M44.5 2.5L49.5 6L44.5 9.5" fill="currentColor" />
                </svg>
            </div>

            <article class="process-step-card">
                <div class="process-step-head">
                    <span class="process-step-num">2</span>
                    <h3 class="process-step-title">Revisi Desain</h3>
                </div>
                <div class="process-step-icon-wrap process-step-icon-wrap--indigo" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7" />
                        <path d="M18.5 2.5a2.12 2.12 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                    </svg>
                </div>
                <p class="process-step-desc">Tim desain menyiapkan draft sesuai kebutuhan Anda. Ajukan revisi sesuai kuota yang tersedia, dengan seluruh riwayat revisi tersimpan di sistem.</p>
            </article>

            <div class="process-connector-mobile" aria-hidden="true">
                <svg viewBox="0 0 12 52" fill="none">
                    <circle cx="6" cy="6" r="4.5" stroke="currentColor" stroke-width="1.5" />
                    <path d="M6 12v30" stroke="currentColor" stroke-width="2" stroke-dasharray="4 3" stroke-linecap="round" />
                    <path d="M2.5 44.5L6 49.5L9.5 44.5" fill="currentColor" />
                </svg>
            </div>
            <div class="process-connector" aria-hidden="true">
                <svg viewBox="0 0 52 12" fill="none">
                    <circle cx="6" cy="6" r="4.5" stroke="currentColor" stroke-width="1.5" />
                    <path d="M12 6h30" stroke="currentColor" stroke-width="2" stroke-dasharray="4 3" stroke-linecap="round" />
                    <path d="M44.5 2.5L49.5 6L44.5 9.5" fill="currentColor" />
                </svg>
            </div>

            <article class="process-step-card">
                <div class="process-step-head">
                    <span class="process-step-num">3</span>
                    <h3 class="process-step-title">Tracking pemesanan</h3>
                </div>
                <div class="process-step-icon-wrap process-step-icon-wrap--cyan" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="9" />
                        <path d="M12 7v5l3 2" />
                        <path d="M3 12h2M19 12h2M12 3v2M12 19v2" />
                    </svg>
                </div>
                <p class="process-step-desc">Pantau perkembangan pesanan secara real-time mulai dari proses desain, produksi, hingga finishing tanpa perlu menghubungi admin.</p>
            </article>

            <div class="process-connector-mobile" aria-hidden="true">
                <svg viewBox="0 0 12 52" fill="none">
                    <circle cx="6" cy="6" r="4.5" stroke="currentColor" stroke-width="1.5" />
                    <path d="M6 12v30" stroke="currentColor" stroke-width="2" stroke-dasharray="4 3" stroke-linecap="round" />
                    <path d="M2.5 44.5L6 49.5L9.5 44.5" fill="currentColor" />
                </svg>
            </div>
            <div class="process-connector" aria-hidden="true">
                <svg viewBox="0 0 52 12" fill="none">
                    <circle cx="6" cy="6" r="4.5" stroke="currentColor" stroke-width="1.5" />
                    <path d="M12 6h30" stroke="currentColor" stroke-width="2" stroke-dasharray="4 3" stroke-linecap="round" />
                    <path d="M44.5 2.5L49.5 6L44.5 9.5" fill="currentColor" />
                </svg>
            </div>

            <article class="process-step-card">
                <div class="process-step-head">
                    <span class="process-step-num">4</span>
                    <h3 class="process-step-title">Pembayaran Pelunasan</h3>
                </div>
                <div class="process-step-icon-wrap process-step-icon-wrap--navy" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="2" y="5" width="20" height="14" rx="2" />
                        <path d="M2 10h20" />
                        <path d="M6 15h4" />
                    </svg>
                </div>
                <p class="process-step-desc">Setelah produksi selesai, unggah bukti pelunasan melalui sistem. Pembayaran akan diverifikasi sebelum pesanan diproses untuk pengiriman atau pengambilan.</p>
            </article>

            <div class="process-connector-mobile" aria-hidden="true">
                <svg viewBox="0 0 12 52" fill="none">
                    <circle cx="6" cy="6" r="4.5" stroke="currentColor" stroke-width="1.5" />
                    <path d="M6 12v30" stroke="currentColor" stroke-width="2" stroke-dasharray="4 3" stroke-linecap="round" />
                    <path d="M2.5 44.5L6 49.5L9.5 44.5" fill="currentColor" />
                </svg>
            </div>
            <div class="process-connector" aria-hidden="true">
                <svg viewBox="0 0 52 12" fill="none">
                    <circle cx="6" cy="6" r="4.5" stroke="currentColor" stroke-width="1.5" />
                    <path d="M12 6h30" stroke="currentColor" stroke-width="2" stroke-dasharray="4 3" stroke-linecap="round" />
                    <path d="M44.5 2.5L49.5 6L44.5 9.5" fill="currentColor" />
                </svg>
            </div>

            <article class="process-step-card">
                <div class="process-step-head">
                    <span class="process-step-num">5</span>
                    <h3 class="process-step-title">Pengiriman</h3>
                </div>
                <div class="process-step-icon-wrap process-step-icon-wrap--teal" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 17h11" />
                        <path d="M14 17h7l-2-5H9l-1 3" />
                        <circle cx="7" cy="17" r="2" />
                        <circle cx="17" cy="17" r="2" />
                        <path d="M5 11h9l2-6h4" />
                    </svg>
                </div>
                <p class="process-step-desc">Pesanan dikirim melalui kurir pilihan atau dapat diambil langsung di Z'Plack. Status pengiriman dan nomor resi tercatat di sistem.</p>
            </article>
        </div>
    </div>
</section>

<?php
$faqItems = [
    [
        'question' => 'Bagaimana cara mendaftar di SIMENAK?',
        'answer' => 'Klik Daftar pada halaman utama, lengkapi data yang diperlukan, lalu akun dapat langsung digunakan.',
    ],
    [
        'question' => 'Bagaimana cara login ke SIMENAK?',
        'answer' => 'Klik Masuk, masukkan email dan kata sandi, kemudian sistem akan mengarahkan Anda ke dashboard. Tersedia juga opsi masuk menggunakan akun Google untuk proses yang lebih cepat.',
    ],
    [
        'question' => 'Bisakah saya masuk menggunakan akun Google?',
        'answer' => 'Bisa. Klik tombol Lanjutkan dengan Google di halaman masuk, pilih akun Google yang terdaftar, dan sistem akan langsung mengenali akun Anda. Fitur ini hanya  tersedia untuk login,pendaftaran akun baru tetap dilakukan melalui form registrasi.',
    ],
    [
        'question' => 'Bagaimana jika saya lupa kata sandi?',
        'answer' => 'Klik Lupa Kata Sandi di halaman masuk, masukkan email yang terdaftar, kemudian sistem akan mengirimkan tautan reset kata sandi ke alamat email tersebut. Tautan berlaku selama 30 menit.',
    ],
    [
        'question' => 'Bagaimana cara melakukan pemesanan?',
        'answer' => 'Pilih produk dari katalog, tentukan jenis pesanan (Standar atau Custom), lengkapi detail pesanan, lalu lakukan pembayaran DP 50% untuk mengonfirmasi pesanan.',
    ],
    [
        'question' => 'Bagaimana cara melakukan pemesanan Custom?',
        'answer' => 'Pilih produk dari katalog, tentukan jenis pesanan (Pilih Pesanan Custom), jelaskan spesifikasi yang diinginkan. Admin akan meninjau permintaan, menetapkan harga, estimasi pengerjaan, sebelum pesanan dapat dikonfirmasi.',
    ],
    [
        'question' => 'Bagaimana cara mengetahui progres pesanan?',
        'answer' => 'Status pesanan dapat dipantau langsung melalui fitur Tracking Pemesanan, mulai dari proses desain hingga pengiriman.',
    ],
    [
        'question' => 'Bagaimana proses revisi desain dilakukan?',
        'answer' => 'Setelah draft diunggah, pelanggan dapat menyetujui atau mengajukan revisi sesuai kuota yang tersedia. Seluruh riwayat revisi tersimpan di sistem.',
    ],
    [
        'question' => 'Bagaimana proses pembayaran dilakukan?',
        'answer' => 'Pembayaran terdiri dari DP 50% dan pelunasan. Bukti pembayaran diunggah melalui sistem dan akan diverifikasi oleh tim keuangan.',
    ],
    [
        'question' => 'Bagaimana pesanan diterima?',
        'answer' => "Setelah pelunasan terverifikasi, pesanan akan dikirim melalui kurir atau dapat diambil langsung di Z'Plack. Status pengiriman dan nomor resi tercatat dalam sistem.",
    ],
];
?>

<section id="faq" class="relative z-10 bg-white py-16 md:py-20">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="faq-heading">Pertanyaan yang sering diajukan</h2>
        <p class="mt-3 text-sm md:text-base text-[#64748b] leading-relaxed">
            Berisikan kumpulan jawaban dari pertanyaan yang sering ditanyakan terkait SIMENAK.
        </p>

        <div class="faq-list">
            <?php foreach ($faqItems as $faq): ?>
                <details class="faq-item group">
                    <summary class="faq-question">
                        <span><?= esc($faq['question']) ?></span>
                        <svg class="faq-chevron" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                    </summary>
                    <p class="faq-answer"><?= esc($faq['answer']) ?></p>
                </details>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section id="portfolio" class="py-16 md:py-20 bg-white overflow-hidden">
    <?php
    $portfolioProjects = [
        ['title' => 'Undangan Pernikahan', 'image' => base_url('assets/1.png')],
        ['title' => 'Brosur Perusahaan', 'image' => base_url('assets/3.png')],
        ['title' => 'Kartu Nama Premium', 'image' => base_url('assets/illustration2.png')],
        ['title' => 'Banner Promosi', 'image' => base_url('assets/ilustrastion.png')],
        ['title' => 'Kalender Meja', 'image' => base_url('assets/ilustrastion2.png')],
        ['title' => 'Packaging Produk', 'image' => base_url('assets/1.png')],
    ];
    ?>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <p class="text-[11px] uppercase tracking-[0.2em] text-[#2E5CE6] font-bold">Our Projects</p>
            <h2 class="portfolio-heading mt-2">Proyek Terbaru yang Telah Kami Selesaikan.</h2>
        </div>

        <div id="projectSlider" class="project-slider">
            <div class="project-slider__viewport">
                <div class="project-slider__track">
                    <?php foreach ($portfolioProjects as $project): ?>
                        <article class="project-card">
                            <div class="project-card__shell">
                                <div class="project-card__accent" aria-hidden="true"></div>
                                <div class="project-card__body">
                                    <div class="project-card__media">
                                        <img src="<?= esc($project['image']) ?>" alt="<?= esc($project['title']) ?>" loading="lazy">
                                        <div class="project-card__overlay" aria-hidden="true">
                                            <span class="project-card__link-icon">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                                                </svg>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <h3 class="project-card__title"><?= esc($project['title']) ?></h3>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="project-slider__nav">
                <button type="button" class="project-slider__btn" data-project-prev aria-label="Proyek sebelumnya">&lsaquo;</button>
                <button type="button" class="project-slider__btn" data-project-next aria-label="Proyek berikutnya">&rsaquo;</button>
            </div>
        </div>
    </div>
</section>

<section id="catalog" class="py-16 md:py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-xl">
            <span class="catalog-badge">TOKO KAMI</span>
            <h2 class="catalog-heading mt-5">Katalog Percetakan</h2>
            <p class="mt-5 text-[14px] text-[#64748b] leading-relaxed">
                Pilih paket produk andalan kami, lalu pesan secara digital.
            </p>
        </div>

        <div class="mt-8 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="catalog-tab-scroll flex min-w-0 flex-nowrap gap-2 overflow-x-auto pb-0.5 lg:flex-wrap lg:overflow-visible">
                <?php foreach ($tabLabels as $key => $label): ?>
                    <button
                        type="button"
                        class="catalog-tab px-4 py-2 rounded-full text-[11px] font-bold uppercase tracking-[0.14em] transition-colors <?= $key === 'all' ? 'bg-[#051747] text-white border border-[#051747]' : 'bg-white text-slate-600 border border-slate-200 hover:border-slate-300' ?>"
                        data-tab="<?= esc($key) ?>">
                        <?= esc($label) ?>
                    </button>
                <?php endforeach; ?>
            </div>

            <div class="flex w-full flex-col gap-2 sm:ml-auto sm:w-auto sm:flex-row sm:items-center sm:gap-2">
                <div class="w-full shrink-0 sm:min-w-[200px] sm:max-w-[300px]">
                    <label for="catalogSearchInput" class="sr-only">Cari katalog</label>
                    <div
                        id="catalogSearchBar"
                        class="flex w-full items-center gap-2 rounded-full border bg-white py-1 pl-3.5 pr-1">
                        <svg class="h-4 w-4 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M11 18a7 7 0 100-14 7 7 0 000 14z" />
                        </svg>
                        <input
                            id="catalogSearchInput"
                            type="search"
                            placeholder="Cari..."
                            autocomplete="off"
                            class="min-w-0 flex-1 border-0 bg-transparent py-2 text-sm text-[#051747] placeholder:text-slate-400 focus:outline-none focus:ring-0">
                        <button
                            type="button"
                            id="catalogSearchBtn"
                            class="shrink-0 rounded-full bg-[#051747] px-4 py-2 text-[10px] font-bold uppercase tracking-[0.12em] text-white transition-colors hover:bg-[#2E5CE6]">
                            Cari
                        </button>
                    </div>
                </div>

                <a href="<?= site_url('katalog') ?>" class="inline-flex shrink-0 items-center justify-center rounded-full bg-[#051747] px-6 py-2.5 text-xs font-bold uppercase tracking-[0.12em] text-white transition-all hover:bg-[#2E5CE6]">
                    Lihat Seluruh Katalog
                </a>
            </div>
        </div>

        <div class="mt-8 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-5 lg:gap-6" id="catalogGrid">
            <?php foreach ($katalogAktif as $item): ?>
                <?php
                $idKatalog     = (int) ($item['id_katalog'] ?? 0);
                $category      = (string) ($item['kategori'] ?? '');
                $namaProduk    = (string) ($item['nama_produk'] ?? '-');
                $categoryLabel = $kategoriLabelMap[$category] ?? str_replace('_', ' ', $category);
                $gambarRaw     = trim((string) ($item['gambar'] ?? ''));
                $gambarUrl     = null;

                if ($gambarRaw !== '') {
                    $normalized = ltrim(str_replace('\\', '/', $gambarRaw), '/');

                    if (str_starts_with($normalized, 'uploads/katalog/')) {
                        $gambarRelPath = substr($normalized, strlen('uploads/katalog/'));
                    } elseif (str_starts_with($normalized, 'katalog/')) {
                        $gambarRelPath = substr($normalized, strlen('katalog/'));
                    } else {
                        $gambarRelPath = $normalized;
                    }

                    $gambarRelPath = ltrim($gambarRelPath, '/');

                    if ($gambarRelPath !== '' && is_file(FCPATH . 'uploads/katalog/' . $gambarRelPath)) {
                        $gambarUrl = base_url('uploads/katalog/' . $gambarRelPath);
                    }
                }

                $hargaDasar = (float) ($item['harga_dasar'] ?? 0);
                $satuan     = (string) ($item['satuan'] ?? 'pcs');
                $minOrder   = (int) ($item['min_order'] ?? 1);
                $deskripsi  = trim((string) ($item['deskripsi'] ?? ''));

                $badgeLabel = $kategoriTabShort[$category]
                    ?? ($categoryLabel !== '' ? $categoryLabel : 'Produk');
                $badgeStyle = $kategoriBadgeStyle[$category] ?? $kategoriBadgeStyleDefault;
                ?>
                <article
                    class="katalog-card catalog-item flex flex-col h-full"
                    data-category="<?= esc($category) ?>"
                    data-name="<?= esc(mb_strtolower($namaProduk)) ?>"
                    data-category-label="<?= esc(mb_strtolower($categoryLabel)) ?>"
                    data-search="<?= esc(mb_strtolower(trim($namaProduk . ' ' . $categoryLabel . ' ' . $deskripsi))) ?>">
                    <div class="katalog-card-media">
                        <span class="katalog-card-badge" style="background-color: <?= esc($badgeStyle['bg']) ?>; color: <?= esc($badgeStyle['text']) ?>">
                            <?= esc($badgeLabel) ?>
                        </span>
                        <?php if ($gambarUrl !== null): ?>
                            <button
                                type="button"
                                class="katalog-card-image-zoom js-catalog-image-zoom"
                                data-zoom-src="<?= esc($gambarUrl) ?>"
                                data-zoom-alt="<?= esc($namaProduk) ?>"
                                aria-label="Perbesar gambar <?= esc($namaProduk) ?>">
                                <img src="<?= esc($gambarUrl) ?>" alt="<?= esc($namaProduk) ?>">
                            </button>
                        <?php else: ?>
                            <div class="katalog-card-placeholder" aria-hidden="true">
                                <svg class="w-14 h-14 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.25">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="flex flex-col flex-1 p-4 sm:p-5">
                        <h3 class="text-sm font-extrabold text-[#051747] uppercase leading-snug tracking-wide">
                            <?= esc($namaProduk) ?>
                        </h3>
                        <p class="mt-1 text-[11px] font-bold uppercase tracking-[0.14em] text-[#2E5CE6]">
                            <?= esc($categoryLabel) ?>
                        </p>

                        <?php if ($deskripsi !== ''): ?>
                            <p class="mt-2 text-xs leading-relaxed text-slate-500 line-clamp-2 flex-1"><?= esc($deskripsi) ?></p>
                        <?php else: ?>
                            <div class="mt-2 flex-1" aria-hidden="true"></div>
                        <?php endif; ?>

                        <div class="mt-4">
                            <p class="text-xs text-slate-500">Mulai</p>
                            <p class="mt-0.5 text-xl sm:text-2xl font-extrabold text-[#051747] leading-tight">
                                Rp <?= esc(number_format($hargaDasar, 0, ',', '.')) ?>
                                <span class="text-sm font-semibold text-slate-400">/<?= esc($satuan) ?></span>
                            </p>
                            <span class="katalog-min-order-tag">Min. <?= esc((string) $minOrder) ?> <?= esc($satuan) ?></span>
                        </div>

                        <button
                            type="button"
                            data-open-modal="<?= $isLoggedIn ? '' : 'loginModal' ?>"
                            onclick="<?= $isLoggedIn ? "window.location.href='" . site_url('order/create/' . $idKatalog) . "'" : '' ?>"
                            class="mt-4 w-full inline-flex items-center justify-center rounded-full border-[1.5px] border-[#051747] bg-white py-2.5 text-[10px] font-bold uppercase tracking-[0.08em] text-[#051747] transition-colors hover:bg-[#051747] hover:text-white">
                            Pilih &amp; Pesan Sekarang
                        </button>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
        <p id="catalogEmpty" class="hidden mt-10 text-center text-sm text-slate-500">
            Produk tidak ditemukan. Coba kata kunci lain atau pilih kategori berbeda.
        </p>
    </div>

    <div id="catalogImageLightbox" role="dialog" aria-modal="true" aria-label="Pratinjau gambar produk">
        <button type="button" id="catalogImageLightboxClose" aria-label="Tutup pratinjau">&times;</button>
        <img id="catalogImageLightboxImg" src="" alt="">
        <p id="catalogImageLightboxCaption" class="hidden"></p>
    </div>
</section>

<section class="py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="premium-cta-card card p-8 md:p-10 text-white">
            <div aria-hidden="true" class="premium-cta-card__mesh absolute inset-0 overflow-hidden">
                <div class="premium-cta-card__blob premium-cta-card__blob--indigo"></div>
                <div class="premium-cta-card__blob premium-cta-card__blob--cyan"></div>
                <div class="premium-cta-card__blob premium-cta-card__blob--blue"></div>
            </div>
            <div class="relative z-[1] flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
                <div>
                    <p class="text-xs uppercase tracking-[0.2em] text-white/70 font-bold">Pengalaman Cetak Premium</p>
                    <h3 class="mt-2 text-3xl md:text-4xl font-extrabold">Wujudkan Kebutuhan Cetak Anda Bersama Kami</h3>
                    <p class="mt-3 text-sm text-white/80 max-w-2xl">Diskusikan kebutuhan detail desain, material, dan estimasi pesanan. Tim kami siap membantu dari konsep sampai produk jadi.</p>
                </div>
                <div class="flex gap-3 shrink-0">
                    <button type="button" data-open-modal="<?= $isLoggedIn ? '' : 'loginModal' ?>" onclick="<?= $isLoggedIn ? "window.location.href='" . site_url('katalog') . "'" : '' ?>" class="btn-primary px-6 py-3 text-xs bg-white !text-[#051747] hover:!bg-slate-200">Pesan Sekarang</button>
                    <a href="#catalog" class="btn-outline px-6 py-3 text-xs !text-white !border-white hover:!bg-white hover:!text-[#051747]">Lihat Katalog</a>
                </div>
            </div>
        </div>
    </div>
</section>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    (() => {
        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            return;
        }

        const revealGroups = [{
                selector: '#hero-landing .hero-copy',
                variant: 'left',
                delay: 0
            },
            {
                selector: '#hero-landing .hero-visual-col',
                variant: 'right',
                delay: 120
            },
            {
                selector: '#about .grid > div:first-child',
                variant: 'up'
            },
            {
                selector: '#about .about-feature-card',
                variant: 'up',
                stagger: 100
            },
            {
                selector: '#services .text-center',
                variant: 'up'
            },
            {
                selector: '#services .service-ref-card',
                variant: 'up',
                stagger: 90
            },
            {
                selector: '#process .text-center',
                variant: 'up'
            },
            {
                selector: '#process .process-step-card',
                variant: 'up',
                stagger: 85
            },
            {
                selector: '#faq .faq-heading',
                variant: 'up'
            },
            {
                selector: '#faq .max-w-3xl > p',
                variant: 'up',
                delay: 80
            },
            {
                selector: '#faq .faq-item',
                variant: 'up',
                stagger: 55
            },
            {
                selector: '#portfolio .text-center',
                variant: 'up'
            },
            {
                selector: '#portfolio .project-slider',
                variant: 'up',
                delay: 100
            },
            {
                selector: '#catalog .max-w-xl',
                variant: 'up'
            },
            {
                selector: '#catalog .max-w-7xl > .mt-8.flex',
                variant: 'up',
                delay: 90
            },
            {
                selector: '#catalog .katalog-card',
                variant: 'up',
                stagger: 75
            },
            {
                selector: '.premium-cta-card',
                variant: 'up'
            },
        ];

        const seen = new Set();
        const elements = [];

        revealGroups.forEach((group) => {
            document.querySelectorAll(group.selector).forEach((el, index) => {
                if (seen.has(el)) {
                    return;
                }

                seen.add(el);
                el.classList.add('scroll-reveal', `scroll-reveal--${group.variant || 'up'}`);

                const delay = (group.delay || 0) + (group.stagger ? index * group.stagger : 0);
                el.style.setProperty('--reveal-delay', `${delay}ms`);
                elements.push(el);
            });
        });

        if (elements.length === 0) {
            return;
        }

        const observer = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (!entry.isIntersecting) {
                        return;
                    }

                    entry.target.classList.add('scroll-reveal--visible');
                    observer.unobserve(entry.target);
                });
            }, {
                threshold: 0.12,
                rootMargin: '0px 0px -6% 0px',
            }
        );

        elements.forEach((el) => observer.observe(el));
    })();

    (() => {
        const tabs = document.querySelectorAll('#catalog .catalog-tab');
        const cards = document.querySelectorAll('#catalog .catalog-item');
        const searchInput = document.getElementById('catalogSearchInput');
        const searchBtn = document.getElementById('catalogSearchBtn');
        const searchBar = document.getElementById('catalogSearchBar');
        const catalogEmpty = document.getElementById('catalogEmpty');
        let activeTab = 'all';

        const setTabActiveStyle = (tab, isActive) => {
            tab.classList.toggle('bg-[#051747]', isActive);
            tab.classList.toggle('text-white', isActive);
            tab.classList.toggle('border-[#051747]', isActive);
            tab.classList.toggle('bg-white', !isActive);
            tab.classList.toggle('text-slate-600', !isActive);
            tab.classList.toggle('border-slate-200', !isActive);
        };

        if (searchBar && searchInput) {
            const setFocused = (focused) => searchBar.classList.toggle('focused', focused);
            searchInput.addEventListener('focus', () => setFocused(true));
            searchInput.addEventListener('blur', () => setFocused(false));
            searchBar.addEventListener('mousedown', (e) => {
                if (e.target === searchBar) {
                    e.preventDefault();
                    searchInput.focus();
                }
            });
        }

        const applyCatalogFilters = () => {
            const query = (searchInput?.value || '').trim().toLowerCase();
            let visibleCount = 0;

            cards.forEach((card) => {
                const category = card.getAttribute('data-category') || '';
                const name = card.getAttribute('data-name') || '';
                const categoryLabel = card.getAttribute('data-category-label') || '';
                const searchBlob = card.getAttribute('data-search') || '';
                const tabMatch = activeTab === 'all' || category === activeTab;
                const searchMatch = query === '' ||
                    name.includes(query) ||
                    category.includes(query) ||
                    categoryLabel.includes(query) ||
                    searchBlob.includes(query);
                const visible = tabMatch && searchMatch;

                card.classList.toggle('hidden', !visible);
                if (visible) {
                    visibleCount++;
                }
            });

            if (catalogEmpty) {
                catalogEmpty.classList.toggle('hidden', visibleCount > 0);
            }
        };

        tabs.forEach((tab) => {
            tab.addEventListener('click', () => {
                activeTab = tab.getAttribute('data-tab') || 'all';

                tabs.forEach((btn) => setTabActiveStyle(btn, btn === tab));

                applyCatalogFilters();
            });
        });

        const runSearch = () => applyCatalogFilters();

        if (searchBtn) {
            searchBtn.addEventListener('click', runSearch);
        }

        if (searchInput) {
            searchInput.addEventListener('input', runSearch);
            searchInput.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    runSearch();
                }
            });
        }

        const lightbox = document.getElementById('catalogImageLightbox');
        const lightboxImg = document.getElementById('catalogImageLightboxImg');
        const lightboxCaption = document.getElementById('catalogImageLightboxCaption');
        const lightboxClose = document.getElementById('catalogImageLightboxClose');

        const closeLightbox = () => {
            if (!lightbox) return;
            lightbox.classList.remove('is-open');
            document.body.classList.remove('overflow-hidden');
            if (lightboxImg) {
                lightboxImg.src = '';
                lightboxImg.alt = '';
            }
            if (lightboxCaption) {
                lightboxCaption.textContent = '';
                lightboxCaption.classList.add('hidden');
            }
        };

        const openLightbox = (src, alt) => {
            if (!lightbox || !lightboxImg || !src) return;
            lightboxImg.src = src;
            lightboxImg.alt = alt || 'Gambar produk';
            if (lightboxCaption) {
                if (alt) {
                    lightboxCaption.textContent = alt;
                    lightboxCaption.classList.remove('hidden');
                } else {
                    lightboxCaption.classList.add('hidden');
                }
            }
            lightbox.classList.add('is-open');
            document.body.classList.add('overflow-hidden');
            lightboxClose?.focus();
        };

        document.querySelectorAll('#catalog .js-catalog-image-zoom').forEach((btn) => {
            btn.addEventListener('click', () => {
                openLightbox(btn.getAttribute('data-zoom-src') || '', btn.getAttribute('data-zoom-alt') || '');
            });
        });

        lightboxClose?.addEventListener('click', closeLightbox);

        lightbox?.addEventListener('click', (e) => {
            if (e.target === lightbox) {
                closeLightbox();
            }
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && lightbox?.classList.contains('is-open')) {
                closeLightbox();
            }
        });
    })();

    (() => {
        const slider = document.getElementById('projectSlider');
        if (!slider) return;

        const track = slider.querySelector('.project-slider__track');
        const cards = [...slider.querySelectorAll('.project-card')];
        const prevBtn = slider.querySelector('[data-project-prev]');
        const nextBtn = slider.querySelector('[data-project-next]');
        const AUTOPLAY_MS = 2000;
        let index = 0;
        let perView = 1;
        let autoplayTimer = null;

        const getPerView = () => {
            if (window.innerWidth >= 1024) return 3;
            if (window.innerWidth >= 640) return 2;
            return 1;
        };

        const getMaxIndex = () => Math.max(0, cards.length - perView);

        const updateButtons = () => {
            if (prevBtn) prevBtn.disabled = index <= 0;
            if (nextBtn) nextBtn.disabled = index >= getMaxIndex();
        };

        const slideTo = (nextIndex) => {
            perView = getPerView();
            index = Math.max(0, Math.min(nextIndex, getMaxIndex()));
            const cardWidth = cards[0]?.getBoundingClientRect().width || 0;
            track.style.transform = `translate3d(-${index * cardWidth}px, 0, 0)`;
            updateButtons();
        };

        const bindParallax = (card) => {
            const media = card.querySelector('.project-card__media img');
            if (!media) return;

            card.addEventListener('mousemove', (e) => {
                const rect = card.getBoundingClientRect();
                const x = ((e.clientX - rect.left) / rect.width - 0.5) * 14;
                const y = ((e.clientY - rect.top) / rect.height - 0.5) * 14;
                media.style.transform = `scale(1.08) translate3d(${x}px, ${y}px, 0)`;
            });

            card.addEventListener('mouseleave', () => {
                media.style.transform = 'scale(1) translate3d(0, 0, 0)';
            });
        };

        const startAutoplay = () => {
            stopAutoplay();
            autoplayTimer = setInterval(() => {
                const max = getMaxIndex();
                slideTo(index >= max ? 0 : index + 1);
            }, AUTOPLAY_MS);
        };

        const stopAutoplay = () => {
            if (autoplayTimer !== null) {
                clearInterval(autoplayTimer);
                autoplayTimer = null;
            }
        };

        const resetAutoplay = () => {
            stopAutoplay();
            startAutoplay();
        };

        cards.forEach((card) => {
            bindParallax(card);

            card.addEventListener('mouseenter', () => {
                cards.forEach((c) => c.classList.remove('is-active'));
                card.classList.add('is-active');
                stopAutoplay();
            });

            card.addEventListener('mouseleave', () => {
                card.classList.remove('is-active');
            });
        });

        slider.addEventListener('mouseleave', startAutoplay);

        prevBtn?.addEventListener('click', () => {
            slideTo(index - 1);
            resetAutoplay();
        });
        nextBtn?.addEventListener('click', () => {
            slideTo(index + 1);
            resetAutoplay();
        });

        window.addEventListener('resize', () => slideTo(index));

        slideTo(0);
        startAutoplay();
    })();
</script>
<?= $this->endSection() ?>