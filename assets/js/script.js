document.addEventListener('DOMContentLoaded', function() {
  // Attach click event listener to all breed items
  const breedItems = document.querySelectorAll('.cat-breed-item');
  const breedSearchInput = document.getElementById('breed-search');
  const breedExample = document.getElementById('breed-example');

  // Update breed example on breed item click
  breedItems.forEach(function(item) {
    item.addEventListener('click', function() {
      // Get the breed name from the data attribute
      const breedId = item.getAttribute('data-breed-id');
      const breedName = item.getAttribute('data-breed-name');

      // Update the example text
      breedExample.innerHTML = `Example: <strong>[cat_gram breed="${breedId.toLowerCase()}" class="cat-img"]</strong> for <span style="color: red;">${breedName}</span> cat breed.`;

      breedExample.scrollIntoView({
        behavior: 'smooth',
        block: 'center'
      });
    });
  });

  // Filter the breeds based on search input
  breedSearchInput.addEventListener('input', function() {
    const searchTerm = breedSearchInput.value.toLowerCase();

    // Loop through each breed item and hide or show based on the search term
    breedItems.forEach(function(item) {
      const breedName = item.getAttribute('data-breed-name').toLowerCase();

      if (breedName.includes(searchTerm)) {
        item.style.display = '';
      } else {
        item.style.display = 'none';
      }
    });
  });

  const apiTab = document.getElementById('api-tab');
  const breedsTab = document.getElementById('breeds-tab');
  const apiContent = document.getElementById('api-key-content');
  const breedsContent = document.getElementById('cat-breeds-content');

  // Switch to the API Key tab
  apiTab.addEventListener('click', function(e) {
    e.preventDefault();
    apiTab.classList.add('nav-tab-active');
    breedsTab.classList.remove('nav-tab-active');
    apiContent.style.display = 'block';
    breedsContent.style.display = 'none';
  });

  // Switch to the Cat Breeds tab
  breedsTab.addEventListener('click', function(e) {
    e.preventDefault();
    breedsTab.classList.add('nav-tab-active');
    apiTab.classList.remove('nav-tab-active');
    breedsContent.style.display = 'block';
    apiContent.style.display = 'none';
  });
});