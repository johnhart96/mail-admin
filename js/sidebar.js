/**
 * sidebar.js - Memory and Toggle Logic
 */
const initTreeMenu = () => {
    const carets = document.querySelectorAll("#myTree .caret");
    const storageKey = 'openTreeNodes';

    // 1. Restore State from LocalStorage
    const restoreState = () => {
        const activeIds = JSON.parse(localStorage.getItem(storageKey)) || [];
        activeIds.forEach(id => {
            const el = document.getElementById(id);
            if (el) {
                el.classList.add("caret-down");
                const nested = el.parentElement.querySelector(".nested");
                if (nested) nested.classList.add("active");
            }
        });
    };

    // 2. Toggle Function
    const toggleNode = function() {
        const nested = this.parentElement.querySelector(".nested");
        if (!nested) return;

        this.classList.toggle("caret-down");
        nested.classList.toggle("active");

        let currentActive = JSON.parse(localStorage.getItem(storageKey)) || [];
        const nodeId = this.id;

        if (this.classList.contains("caret-down")) {
            if (!currentActive.includes(nodeId)) currentActive.push(nodeId);
        } else {
            currentActive = currentActive.filter(id => id !== nodeId);
        }
        localStorage.setItem(storageKey, JSON.stringify(currentActive));
    };

    // Attach listeners and restore
    carets.forEach(caret => caret.addEventListener("click", toggleNode));
    restoreState();
};

// Initialize when DOM is ready
if (document.readyState === "complete" || document.readyState === "interactive") {
    initTreeMenu();
} else {
    document.addEventListener("DOMContentLoaded", initTreeMenu);
}