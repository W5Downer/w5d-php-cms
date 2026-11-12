/**************************************************
==================== JS START HERE ================
***************************************************/

(function ($) {
	"use strict";

	var windowOn = $(window);

	// ===== PRELOADER CONTROL =====
	windowOn.on("load", () => {
		const preloader = $("#preloader");
		setTimeout(() => {
			gsapHandeller();
			preloader
				.removeClass("is-loading")
				.addClass("is-loaded")
				.fadeOut(300);
		}, 1500);
	});


	$(document).ready(function () {

		const $offcanvas = $('.oitoffcanvas');
		const $overlay = $('.body-overlay');

		$('.oit-menu-bar').on('click', function () {
			$offcanvas.addClass('opened');
			$overlay.addClass('apply');
		});

		$('.close-btn, .body-overlay').on('click', function () {
			$offcanvas.removeClass('opened');
			$overlay.removeClass('apply');
			$('.search__popup').removeClass('search-opened');
		});

	});



	// mobile menu
	(() => {
		if (!$('.oit-menu-content').length || !$('.oit-menu-mobile').length) return;
		const $mobileMenuContainer = $('.oit-menu-mobile');
		const $desktopMenuContent = $('.oit-menu-content');
		$mobileMenuContainer.html($desktopMenuContent.html());
		$mobileMenuContainer.find('.has-dropdown > a').append(`
			<button class="dropdown-toggle-btn">
				<i class="fal fa-angle-right"></i>
			</button>
		`);
		$mobileMenuContainer.on('click', '.dropdown-toggle-btn', function (event) {
			event.preventDefault();
			const $toggleButton = $(this);
			const $menuItem = $toggleButton.closest('.has-dropdown');
			const $submenu = $menuItem.children('.oit-submenu');
			$menuItem
				.toggleClass('expanded')
				.siblings('.has-dropdown')
				.removeClass('expanded')
				.children('.oit-submenu')
				.slideUp();
			$submenu.stop(true, true).slideToggle();
		});
	})();

	// Common Js//
	function applyDataStyle(attribute, cssProperty, isUrl = false) {
		$(`[data-${attribute}]`).each(function () {
			const value = $(this).data(attribute);
			$(this).css(
				cssProperty,
				isUrl ? `url(${value})` : value
			);
		});
	}
	// Usage
	applyDataStyle('background', 'background-image', true);
	applyDataStyle('width', 'width');
	applyDataStyle('bg-color', 'background-color');
	applyDataStyle('text-color', 'color');



	// Nice Select Js//
	$('select').niceSelect();


	// Counter Js //
	new PureCounter();
	new PureCounter({
		filesizing: true,
		selector: ".filesizecount",
		pulse: 2,
	});

	// initSmoothScroll
	function initSmoothScroll() {
		$('.smooth-scroll').on('click', function (event) {
			const targetId = $(this).attr('href');
			const targetElement = $(targetId);
			if (targetElement.length) {
				event.preventDefault();
				$('html, body').stop().animate({
					scrollTop: targetElement.offset().top - 0
				},
					1000
				);
			}
		});
	}
	initSmoothScroll();


	//  isotope
	$('.grid').imagesLoaded(function () {
		var $grid = $('.grid').isotope({
			itemSelector: '.grid-item',
			percentPosition: true,
			masonry: {
				columnWidth: '.grid-item',
			},

		});
		$('.masonary-menu').on('click', 'button', function () {
			var filterValue = $(this).attr('data-filter');
			$grid.isotope({
				filter: filterValue,
				animationOptions: {
					duration: 100,
					easing: "linear",
					queue: true
				}
			});

		});
		$('.masonary-menu button').on('click', function (event) {
			$(this).siblings('.active').removeClass('active');
			$(this).addClass('active');
			event.preventDefault();
		});

	});


	// ScrollSmoother
	gsap.registerPlugin(ScrollTrigger, ScrollSmoother, ScrollToPlugin, CustomEase);

	if ($('#smooth-wrapper, #smooth-content').length === 2) {
		gsap.config({ nullTargetWarn: false });

		ScrollSmoother.create({
			smooth: 1.35,
			effects: true,
			smoothTouch: 1,
			normalizeScroll: true
		});
	}


	function gsapHandeller(){

		// gsap common variable
		let tl = gsap.timeline();
		let mm = gsap.matchMedia();

		/* ========== rotate ANIMATION ========== */
		document.querySelectorAll('.rotate-on-scroll').forEach(el => {
		const section = el.closest('.rotate-section');
			if (!section) return;
			ScrollTrigger.create({
				trigger: section,
				start: "top bottom",
				end: "bottom top",
				onUpdate: self => {
				const rotation = 360 * self.progress;
				el.style.transform = `rotate(${rotation}deg)`;
				}
			});
		});

		/* ========== FADE ANIMATION ========== */
		gsap.utils.toArray(".fade-anim").forEach(item => {
			const offset  = item.getAttribute("data-fade-offset") || 40,
				duration  = item.getAttribute("data-duration") || 0.75,
				direction = item.getAttribute("data-fade-from") || "bottom",
				onScroll  = item.getAttribute("data-on-scroll") || "1",
				delay     = item.getAttribute("data-delay") || 0.15,
				ease      = item.getAttribute("data-ease") || "power2.out";
			const animSettings = {
				opacity: 0,
				ease,
				duration,
				delay,
				x: direction === "left" ? -offset : direction === "right" ? offset : 0,
				y: direction === "top" ? -offset : direction === "bottom" ? offset : 0,
			};

			if (onScroll === "1") {
				animSettings.scrollTrigger = {
					trigger: item,
					start: "top 85%"
				};
			}
			gsap.from(item, animSettings);
		});
	
		//  handle data-speed attr
		let speedXTriggers = [];
		function initSpeedX() {
			speedXTriggers.forEach(st => st.kill());
			speedXTriggers = [];
			if (window.innerWidth < 1200) return;
			gsap.utils.toArray("[data-speed-x]").forEach(el => {
				const speedX = parseFloat(el.dataset.speedX) || 0;
				const st = ScrollTrigger.create({
					trigger: el,
					scrub: true,
					onUpdate: (self) => {
						const progress = self.progress;
						const move = progress * speedX * 300;
						gsap.set(el, { x: -move });
					}
				});
				speedXTriggers.push(st);
			});
		}
		function handleDataSpeedAttr() {
			const elements = document.querySelectorAll("[data-speed], [data-speed-original]");
			elements.forEach(el => {
				if (!el.dataset.speedOriginal && el.dataset.speed) {
					el.dataset.speedOriginal = el.dataset.speed;
				}
				if (window.innerWidth < 1200) {
					el.removeAttribute("data-speed");
				} else {
					if (el.dataset.speedOriginal) {
						el.setAttribute("data-speed", el.dataset.speedOriginal);
					}
				}
			});
			initSpeedX();
		}
		handleDataSpeedAttr();
		window.addEventListener("resize", () => { 
			handleDataSpeedAttr();
			ScrollTrigger.refresh();
		});
	
	
		/* ========== custom pin section ========== */
		mm.add("(min-width: 1199px)", () => {
			let panels = document.querySelectorAll('.oit-panel-pin')
			panels.forEach((section, index) => {
				let startVal = section.dataset.start || 'top top'
				let endVal = section.dataset.end || 'bottom 100%'
				gsap.fromTo(
					section,
					{
						transformOrigin: '100% 0% 0px',
						x: 0,
						y: 0,
						rotate: 0,
						scale: 1,
					},
					{
						yPercent: 5,
						rotate: 20,
						scale: 0.75,
						ease: 'none',
						scrollTrigger: {
							trigger: section,
							pin: section,
							scrub: 1,
							start: startVal,
							end: endVal,
							endTrigger: '.oit-panel-pin-area',
							pinSpacing: false,
							markers: false,
						},
					}
				)
			})
		})

		/* ========== simple panel pin ========== */
		mm.add("(min-width: 1199px)", () => {
			let otherSections = document.querySelectorAll('.simple-panel-pin')
			otherSections.forEach((section, index) => {
				tl.to(section, {
					scrollTrigger: {
						trigger: section,
						pin: section,
						scrub: 1,
						start: 'top 10%',
						end: "bottom 100%",
						endTrigger: '.panel-pin-area',
						pinSpacing: false,
						markers: false,
					},
				})
			})
		});
	

	    /* ========== image revel hover item ========== */
		let imageWrapperElement = document.querySelector(".oit-img-wrap");
		let imageSliderElement  = document.querySelector(".oit-img-inner-wrap");
		let projectElements     = gsap.utils.toArray(".oit-team-inner-wrap");
		if (imageWrapperElement && imageSliderElement && projectElements.length) {
			let totalProjects   = imageSliderElement.children.length;
			let slideMovePercent = 100 / totalProjects;
			let toggleImageWrapperVisibility = (opacityValue) => {
					gsap.to(imageWrapperElement, {
					opacity: opacityValue,
					duration: 0.5
				});
			};
			$('[data-index-number]')
				.on("mouseenter", () => toggleImageWrapperVisibility(1))
				.on("mouseleave", () => toggleImageWrapperVisibility(0))
				.on("mousemove", function () {
				let currentIndexNumber = $(this).data("index-number");
				gsap.to(imageSliderElement, {
					y: `-${slideMovePercent * currentIndexNumber}%`,
					duration: 0.6,
					ease: "power2.out"
				});
				});
	
			$(document).on("mousemove", ".team-action-wrap", function (event) {
				let { left: parentLeftOffset, top: parentTopOffset } = $(this).offset();
				gsap.to(imageWrapperElement, {
				x: event.pageX - parentLeftOffset + 50,
				y: event.pageY - parentTopOffset,
				duration: 0.5,
				ease: "power2.out"
				});
			});
		}
	
	    /* ========== TEXT REVEAL ========== */
		document.querySelectorAll(".oit-text-revel").forEach(el => {
			const split = new SplitText(el, { type: "chars" });
			gsap.from(split.chars, {
				opacity: 0.2,
				x: -5,
				stagger: 0.03,
				scrollTrigger: {
					trigger: el,
					start: "top 70%",
					scrub: 0.7
				}
			});
		});
	
		/* ========== TITLE REVEAL ========== */
		document.querySelectorAll(".oit-title-revel").forEach(el => {
			const split = new SplitText(el, { type: "words" });
			gsap.from(split.words, {
				opacity: 0,
				y: 20,
				stagger: 0.09,
				scrollTrigger: {
					trigger: el,
					start: "top 70%",
					scrub: 0.7
				}
			});
		});

		/* ========== REFRESH ========= */
		ScrollTrigger.refresh();

	}

})(jQuery);