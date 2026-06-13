/**
 * Handles the "Gallery" button click.
 * Opens/closes a row showing up to 3 images for the clicked product.
 */

document.addEventListener("DOMContentLoaded", () => {
  const galleryButtons = document.querySelectorAll(".gallery-btn");

  galleryButtons.forEach((button) => {
    button.addEventListener("click", () => {
      const row = button.closest(".product-row");
      const galleryRow = row.nextElementSibling;
      const container = galleryRow.querySelector(".gallery-container");

      const isOpen = galleryRow.style.display === "table-row";

      if (isOpen) {
        galleryRow.style.display = "none";
        container.innerHTML = "";
        button.textContent = "Gallery";
      } else {
        const images = JSON.parse(button.dataset.images || "[]");

        if (images.length === 0) {
          container.innerHTML = "<p class='no-images'>No images available.</p>";
        } else {
          container.innerHTML = images
            .map(
              (src) =>
                `<img src="${src}" class="gallery-image" alt="Product image">`,
            )
            .join("");
        }

        galleryRow.style.display = "table-row";
        button.textContent = "Close";
      }
    });
  });
});
