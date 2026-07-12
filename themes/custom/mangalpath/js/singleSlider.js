(function (Drupal, once) {
  Drupal.behaviors.propertyDetails = {
    attach: function (context, settings) {
      // ===== AOS INIT =====
      if (typeof AOS !== "undefined") {
        AOS.init({
          duration: 700,
          once: true,
          offset: 60,
        });
      }

      // ===== GALLERY IMAGES DATA =====
      // window.galleryImages = window.galleryImages || [
      //     'https://picsum.photos/seed/prop-main-1/1200/800.jpg',
      //     'https://picsum.photos/seed/prop-kitchen-2/1200/800.jpg',
      //     'https://picsum.photos/seed/prop-bedroom-3/1200/800.jpg',
      //     'https://picsum.photos/seed/prop-living-4/1200/800.jpg',
      //     'https://picsum.photos/seed/prop-bath-5/1200/800.jpg',
      //     'https://picsum.photos/seed/prop-balcony-6/1200/800.jpg',
      //     'https://picsum.photos/seed/prop-exterior-7/1200/800.jpg',
      //     'https://picsum.photos/seed/prop-lobby-8/1200/800.jpg'
      // ];
      window.galleryImages = [];

      document.querySelectorAll(".gallery-thumb").forEach(function (img) {
        window.galleryImages.push(img.src);
      });

      window.currentImgIndex = window.currentImgIndex || 0;

      // Keyboard navigation for lightbox
      once("property-lightbox-keyboard", document.body).forEach(function () {
        document.addEventListener("keydown", function (e) {
          if (!document.getElementById("lightbox").classList.contains("show"))
            return;
          if (e.key === "Escape") closeLightbox();
          if (e.key === "ArrowLeft") navLightbox(-1);
          if (e.key === "ArrowRight") navLightbox(1);
        });
      });

      // ===== BACK TO TOP =====
      once("property-scroll", window).forEach(function () {
        window.addEventListener("scroll", function () {
          const btn = document.getElementById("backToTop");
          if (!btn) return;

          if (window.scrollY > 400) {
            btn.classList.add("visible");
          } else {
            btn.classList.remove("visible");
          }
        });
      });

      // ===== ENQUIRY MODAL =====
      once("property-enquiry", "#enquiryModal", context).forEach(
        function (modal) {
          modal.addEventListener("click", function (e) {
            if (e.target === this) {
              closeEnquiry();
            }
          });
        },
      );
    },
  };

  // ===== Change main image from thumbnail =====
  window.changeImage = function (thumbEl, index) {
    document.getElementById("mainImage").src = galleryImages[index];
    currentImgIndex = index;
    document
      .querySelectorAll(".gallery-thumb")
      .forEach((t) => t.classList.remove("active"));
    thumbEl.classList.add("active");
  };

  // ===== LIGHTBOX =====
  window.openLightbox = function (index) {
    currentImgIndex = index;
    updateLightbox();
    document.getElementById("lightbox").classList.add("show");
    document.body.style.overflow = "hidden";
  };

  window.closeLightbox = function () {
    document.getElementById("lightbox").classList.remove("show");
    document.body.style.overflow = "";
  };

  window.navLightbox = function (dir) {
    currentImgIndex += dir;
    if (currentImgIndex < 0) currentImgIndex = galleryImages.length - 1;
    if (currentImgIndex >= galleryImages.length) currentImgIndex = 0;
    updateLightbox();
  };

  window.updateLightbox = function () {
    document.getElementById("lightboxImg").src = galleryImages[currentImgIndex];
    document.getElementById("lightboxCounter").textContent =
      `${currentImgIndex + 1} / ${galleryImages.length}`;
  };

  // ===== TABS =====
  window.switchTab = function (tabId, btnEl) {
    document
      .querySelectorAll(".tab-panel")
      .forEach((p) => p.classList.remove("active"));
    document
      .querySelectorAll(".detail-tab")
      .forEach((t) => t.classList.remove("active"));
    document.getElementById("tab-" + tabId).classList.add("active");
    btnEl.classList.add("active");
  };

  // ===== LIKE / SHARE =====
  window.toggleLike = function () {
    const btn = document.getElementById("likeBtn");
    btn.classList.toggle("liked");
    const isLiked = btn.classList.contains("liked");
    btn.innerHTML = isLiked
      ? '<i class="fa fa-heart"></i> Saved'
      : '<i class="fa fa-heart"></i> Save';

    showToast(
      isLiked
        ? "Property saved to your favorites!"
        : "Property removed from favorites.",
      isLiked ? "fa-heart" : "fa-heart-broken",
    );
  };

  window.shareProperty = function () {
    const btn = document.getElementById("shareBtn");
    btn.classList.add("shared");

    if (navigator.share) {
      navigator
        .share({
          title: "Luxury 3BHK Flat in Civil Lines, Jaipur",
          text: "Check out this property on mangalpath - ₹55,00,000",
          url: window.location.href,
        })
        .catch(() => {});
    } else {
      navigator.clipboard.writeText(window.location.href).then(() => {
        showToast("Link copied to clipboard!", "fa-link");
      });
    }

    setTimeout(() => btn.classList.remove("shared"), 2000);
  };

  window.printPage = function () {
    window.print();
  };

  // ===== ENQUIRY MODAL =====
  window.openEnquiry = function () {
    document.getElementById("enquiryModal").classList.add("show");
    document.body.style.overflow = "hidden";
  };

  window.closeEnquiry = function () {
    document.getElementById("enquiryModal").classList.remove("show");
    document.body.style.overflow = "";
  };

  window.submitEnquiry = function () {
    const name = document.getElementById("eqName").value.trim();
    const phone = document.getElementById("eqPhone").value.trim();

    if (!name || !phone) {
      showToast(
        "Please fill Name and Phone Number.",
        "fa-exclamation-triangle",
      );
      return;
    }

    closeEnquiry();

    showToast(
      "Enquiry sent successfully! We will contact you soon.",
      "fa-check-circle",
    );

    document.getElementById("eqName").value = "";
    document.getElementById("eqPhone").value = "";
    document.getElementById("eqEmail").value = "";
    document.getElementById("eqMsg").value = "";
    document.getElementById("eqVisit").value = "";
  };

  // ===== TOAST =====
  window.showToast = function (msg, icon) {
    const toast = document.getElementById("toastMsg");
    const toastText = document.getElementById("toastText");

    toastText.textContent = msg;

    toast.querySelector("i").className = "fa " + (icon || "fa-check-circle");
    toast.querySelector("i").style.color =
      icon === "fa-exclamation-triangle" ? "#f59e0b" : "var(--green)";

    toast.classList.add("show");

    setTimeout(() => {
      toast.classList.remove("show");
    }, 3500);
  };
})(Drupal, once);
