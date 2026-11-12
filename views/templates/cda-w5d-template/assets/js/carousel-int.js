(function ($) {
	"use strict";


     //* ========== testimonial Swiper ========= */
	const testimonialswiper = new Swiper('.dg-testimonial-active', {
        speed: 1500,
        loop: true,
        slidesPerView: 4,
        spaceBetween: 25,
        autoplay: false,
        breakpoints: {
            '1600': {
                slidesPerView: 4,
            },
            '1400': {
                slidesPerView: 3,
            },
            '1200': {
                slidesPerView: 2,
            },
            '992': {
                slidesPerView: 2,
            },
            '768': {
                slidesPerView: 1,
            },
            '576': {
                slidesPerView: 1,
            },
            '0': {
                slidesPerView: 1,
            },
        },
		
	});


    //* ========== testimonial Swiper ========= */
	const testimonial = new Swiper('.st-testimonial-active', {
        speed: 1500,
        loop: true,
        slidesPerView: 3,
        spaceBetween: 2,
        autoplay: false,
        breakpoints: {
            '1400': {
                slidesPerView: 3,
            },
            '1200': {
                slidesPerView: 2,
            },
            '992': {
                slidesPerView: 2,
            },
            '768': {
                slidesPerView: 2,
            },
            '576': {
                slidesPerView: 1,
            },
            '0': {
                slidesPerView: 1,
            },
        },
		
	});

    //* ========== project Swiper ========= */
	const project = new Swiper('.project-slide-active', {
        speed: 1500,
        loop: true,
        slidesPerView: 5,
        spaceBetween: 25,
        autoplay: true,
        breakpoints: {
            '1400': {
                slidesPerView: 5,
            },
            '1200': {
                slidesPerView: 5,
            },
            '992': {
                slidesPerView: 4,
            },
            '768': {
                slidesPerView: 2,
            },
            '576': {
                slidesPerView: 2,
            },
            '0': {
                slidesPerView: 1,
            },
        },
		
	});

    /* ========== postbox Swiper ========= */
    const postBoxswiper = new Swiper('.postbox-thumb-slider-active', {
        speed: 1500,
        loop: true,
        slidesPerView: 1,
        spaceBetween: 20,
        autoplay: true,
        navigation: {
            prevEl: '.postbox-arrow-prev',
            nextEl: '.postbox-arrow-next',
        },
        
    })


})(jQuery);