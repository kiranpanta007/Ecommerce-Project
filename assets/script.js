document.addEventListener("DOMContentLoaded", function() {
    const searchInput = document.querySelector(".search-bar input[name='query']");
    const suggestionBox = document.getElementById("suggestion");

    searchInput.addEventListener("input", function() {
        const query = searchInput.value.trim();

        if (query.length > 0) {
            fetch(`search_suggestions.php?query=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => {
                    suggestionBox.innerHTML = ""; // Clear previous suggestions

                    if (data.error) {
                        console.error("Error:", data.error);
                        return;
                    }

                    if (data.length > 0) {
                        suggestionBox.style.display = "block";
                        data.forEach(item => {
                            const suggestionItem = document.createElement("div");
                            suggestionItem.classList.add("suggestion-item");
                            suggestionItem.textContent = item;

                            suggestionItem.addEventListener("click", () => {
                                searchInput.value = item;
                                suggestionBox.innerHTML = "";
                                suggestionBox.style.display = "none";
                            });

                            suggestionBox.appendChild(suggestionItem);
                        });
                    } else {
                        suggestionBox.style.display = "none";
                    }
                })
                .catch(error => console.error("Fetch error:", error));
        } else {
            suggestionBox.innerHTML = "";
            suggestionBox.style.display = "none";
        }
    });

    // Close suggestions when clicking outside
    document.addEventListener("click", (e) => {
        if (!suggestionBox.contains(e.target) && e.target !== searchInput) {
            suggestionBox.innerHTML = "";
            suggestionBox.style.display = "none";
        }
    });
});
