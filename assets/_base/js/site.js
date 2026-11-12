/* ==========================================================================
   W5D — site.js
   Vanilla, dependency-free, ~4 KB. Everything degrades gracefully: with
   scripts disabled the menu is still reachable, every language has a real URL,
   and all content is already in the HTML.

   Responsibilities
     1. Mobile navigation toggle
     2. Theme switch (stored in localStorage "w5d-theme")
     3. Language switcher: keep the current page when changing language
     4. Carousel controls (scroll-snap based)
     5. Service catalogue filter
     6. Table-of-contents active-section highlight
     7. Forms: refuse to pretend (disabled until an endpoint exists)
   ========================================================================== */
(function () {
  "use strict";

  var doc = document;
  var root = doc.documentElement;

  root.classList.remove("no-js");
  root.classList.add("js");

  var reduceMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;

  /* ── 1. Navigation ───────────────────────────────────────────────────── */
  var navToggle = doc.querySelector("[data-navtoggle]");
  var nav = doc.querySelector("[data-sitenav]");

  if (navToggle && nav) {
    var setNav = function (open) {
      navToggle.setAttribute("aria-expanded", open ? "true" : "false");
      nav.classList.toggle("is-open", open);
      doc.body.style.overflow = open ? "hidden" : "";
    };

    navToggle.addEventListener("click", function () {
      setNav(navToggle.getAttribute("aria-expanded") !== "true");
    });

    doc.addEventListener("keydown", function (event) {
      if (event.key === "Escape" && navToggle.getAttribute("aria-expanded") === "true") {
        setNav(false);
        navToggle.focus();
      }
    });

    // Close after following a link (useful on mobile).
    nav.addEventListener("click", function (event) {
      var link = event.target.closest("a");
      if (link && window.innerWidth < 992) setNav(false);
    });

    window.addEventListener("resize", function () {
      if (window.innerWidth >= 992) setNav(false);
    });
  }

  /* ── 2. Theme ────────────────────────────────────────────────────────── */
  var themeButton = doc.querySelector("[data-themetoggle]");
  var themeIcon = doc.querySelector("[data-themeicon]");
  var order = ["auto", "light", "dark"];
  var icons = { auto: "◐", light: "☀", dark: "☾" };
  var labels = { auto: "System theme", light: "Light theme", dark: "Dark theme" };

  function readTheme() {
    try {
      return localStorage.getItem("w5d-theme") || "auto";
    } catch (e) {
      return "auto";
    }
  }

  function applyTheme(theme) {
    if (theme === "auto") {
      root.removeAttribute("data-theme");
    } else {
      root.setAttribute("data-theme", theme);
    }
    if (themeIcon) themeIcon.textContent = icons[theme] || icons.auto;
    if (themeButton) {
      themeButton.setAttribute("data-theme-state", theme);
      themeButton.setAttribute("title", labels[theme] || labels.auto);
      themeButton.setAttribute("aria-label", labels[theme] || labels.auto);
    }
  }

  if (themeButton) {
    applyTheme(readTheme());
    themeButton.addEventListener("click", function () {
      var next = order[(order.indexOf(readTheme()) + 1) % order.length];
      try {
        localStorage.setItem("w5d-theme", next);
      } catch (e) {}
      applyTheme(next);
    });
  }

  /* ── 3. Language switcher ─────────────────────────────────────────────── */
  // Every language has a real URL, so the links work on their own. This only
  // refines them when the visitor is deep-linked to an anchor, or when the page
  // was reached at a path whose translation prefix differs from the current one.
  doc.querySelectorAll("[data-langswitch]").forEach(function (details) {
    details.addEventListener("toggle", function () {
      if (!details.open) return;
      doc.querySelectorAll("[data-langswitch]").forEach(function (other) {
        if (other !== details) other.open = false;
      });
    });
  });

  doc.addEventListener("click", function (event) {
    if (!event.target.closest("[data-langswitch]")) {
      doc.querySelectorAll("[data-langswitch][open]").forEach(function (d) {
        d.open = false;
      });
    }
  });

  // Preserve the hash when switching language.
  doc.querySelectorAll("[data-langswitch] a[data-lang]").forEach(function (link) {
    link.addEventListener("click", function () {
      if (window.location.hash) {
        var base = link.getAttribute("href").split("#")[0];
        link.setAttribute("href", base + window.location.hash);
      }
    });
  });

  /* ── 4. Carousel ─────────────────────────────────────────────────────── */
  doc.querySelectorAll("[data-carousel]").forEach(function (carousel) {
    var track = carousel.querySelector("[data-carousel-track]");
    var slides = carousel.querySelectorAll("[data-carousel-slide]");
    var status = carousel.querySelector("[data-carousel-status]");
    var prev = carousel.querySelector("[data-carousel-prev]");
    var next = carousel.querySelector("[data-carousel-next]");
    if (!track || slides.length === 0) return;

    function step(direction) {
      var slide = slides[0];
      var width = slide.getBoundingClientRect().width + 16; // gap
      track.scrollBy({
        left: direction * width,
        behavior: reduceMotion ? "auto" : "smooth"
      });
    }

    function current() {
      var center = track.scrollLeft + track.clientWidth / 2;
      var best = 0;
      var bestDistance = Infinity;
      slides.forEach(function (slide, index) {
        var mid = slide.offsetLeft + slide.offsetWidth / 2;
        var distance = Math.abs(mid - center);
        if (distance < bestDistance) {
          bestDistance = distance;
          best = index;
        }
      });
      return best;
    }

    function sync() {
      if (status) status.textContent = current() + 1 + " / " + slides.length;
    }

    if (prev) prev.addEventListener("click", function () { step(-1); });
    if (next) next.addEventListener("click", function () { step(1); });

    var ticking = false;
    track.addEventListener(
      "scroll",
      function () {
        if (ticking) return;
        ticking = true;
        window.requestAnimationFrame(function () {
          sync();
          ticking = false;
        });
      },
      { passive: true }
    );

    track.addEventListener("keydown", function (event) {
      if (event.key === "ArrowRight") { step(1); event.preventDefault(); }
      if (event.key === "ArrowLeft") { step(-1); event.preventDefault(); }
    });

    sync();
  });

  /* ── 5. Catalogue filter ─────────────────────────────────────────────── */
  doc.querySelectorAll("[data-catalog-filter]").forEach(function (form) {
    var input = form.querySelector("[data-catalog-search]");
    var scope = form.closest(".block--catalog") || doc;
    var items = scope.querySelectorAll("[data-catalog-item]");
    var groups = scope.querySelectorAll("[data-catalog-group]");
    var countOut = form.querySelector("[data-catalog-count]");
    var empty = scope.querySelector("[data-catalog-empty]");
    if (!input) return;

    function run() {
      var query = input.value.trim().toLowerCase();
      var shown = 0;

      items.forEach(function (item) {
        var haystack = (item.getAttribute("data-search") || "").toLowerCase();
        var match = query === "" || haystack.indexOf(query) !== -1;
        item.hidden = !match;
        if (match) shown++;
      });

      groups.forEach(function (group) {
        var visible = group.querySelectorAll("[data-catalog-item]:not([hidden])").length;
        group.hidden = visible === 0;
      });

      if (countOut) {
        countOut.innerHTML =
          "Showing <strong>" + shown + "</strong> of " + items.length;
      }
      if (empty) empty.hidden = shown !== 0;
    }

    input.addEventListener("input", run);
    form.addEventListener("submit", function (event) {
      event.preventDefault();
      run();
    });
  });

  /* ── 6. Table of contents highlight ──────────────────────────────────── */
  var tocLinks = doc.querySelectorAll(".subnav__list a");
  if (tocLinks.length > 0 && "IntersectionObserver" in window) {
    var targets = [];
    tocLinks.forEach(function (link) {
      var id = link.getAttribute("href").slice(1);
      var section = doc.getElementById(id);
      if (section) targets.push({ link: link, section: section });
    });

    var observer = new IntersectionObserver(
      function (entries) {
        entries.forEach(function (entry) {
          if (!entry.isIntersecting) return;
          targets.forEach(function (pair) {
            var active = pair.section === entry.target;
            pair.link.classList.toggle("is-active", active);
            if (active) pair.link.setAttribute("aria-current", "true");
            else pair.link.removeAttribute("aria-current");
          });
        });
      },
      { rootMargin: "-30% 0px -60% 0px", threshold: 0 }
    );

    targets.forEach(function (pair) {
      observer.observe(pair.section);
    });
  }

  /* ── 7. Forms ────────────────────────────────────────────────────────── */
  doc.querySelectorAll("form[data-disabled='true']").forEach(function (form) {
    form.addEventListener("submit", function (event) {
      event.preventDefault();
      var note = form.querySelector(".form__disabled");
      if (note) note.scrollIntoView({ block: "center", behavior: reduceMotion ? "auto" : "smooth" });
    });
  });
})();
