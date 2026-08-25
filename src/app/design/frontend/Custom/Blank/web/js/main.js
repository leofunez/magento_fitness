document.addEventListener('DOMContentLoaded', function() {
    // Header Sticky
    const $headerSticky = document.querySelector(".header__sticky");
    const $mainNavigation = document.querySelector(".main-navigation");

    window.addEventListener("scroll", function() {
        if ($mainNavigation) {
            const { top } = $mainNavigation.getBoundingClientRect();
            
            if (top + window.scrollY < 250) {
                if ($headerSticky.classList.contains("is-active")) $headerSticky.classList.remove("is-active");
            } else {
                if (!$headerSticky.classList.contains("is-active")) $headerSticky.classList.add("is-active");
            }
        }
    });
    // .Header Sticky

    // Search
    const $searchInput = document.querySelector(".search #search");
    $searchInput.setAttribute("placeholder", "Search...");
    // .Search

    // Filter Options
    const $filterOptions = document.querySelectorAll('.filter-options');

    $filterOptions.forEach(function(filterOption) {
        const $optionsTitles = filterOption.querySelectorAll('.filter-options-title');

        $optionsTitles.forEach(function(optionsTitle) {
            optionsTitle.classList.remove('active');
            optionsTitle.nextElementSibling.classList.remove('active');

            optionsTitle.addEventListener('click', function() {
                optionsTitle.classList.toggle('active');
                optionsTitle.nextElementSibling.classList.toggle('active');
            });
        });
    });
    // .Filter Options

    // Quantity
    const $quantityControls = document.querySelectorAll('.quantity__control');
    $quantityControls.forEach(function(quantityControl) {
        const $decrement = quantityControl.querySelector('.decrement');
        const $input = quantityControl.querySelector('.quantity__input');
        const $increment = quantityControl.querySelector('.increment');

        $decrement.addEventListener('click', function() {
            if ($input.value > 1) {
                $input.value = $input.value - 1;
            }
        });

        $increment.addEventListener('click', function() {
            $input.value ++;
        });
    });
    // .Quantity

    // Menu Navigation
    const $mainNavigationContainer = document.querySelector('.nav-sections');
    const $mainNavigationList = $mainNavigationContainer.querySelector('.main-navigation__items');
    const $menuItems = $mainNavigationList.querySelectorAll('.level0.level-top');

    let currentLevelCounter = 0;
    let isMainMenu = true;

    function getCurrentMenuListIndex() {
        const activeElement = document.activeElement.closest(`.level0`);
        const arrayMenuItems = Array.prototype.slice.call($menuItems);
        const currentTopIndex = arrayMenuItems.indexOf(activeElement);

        return currentTopIndex;
    }

    function openSubMenu(list, index = 0) {
        if (!list || list.length <= 0) return;
    
        list.forEach(function(item) {
            item.classList.remove('is-focussed');
        });

        list[index].classList.add('is-focussed');
    }

    function closeAllSubmenu(activeItem) {
        if(!activeItem) return;
        activeItem.classList.remove('is-focussed');

        const $subMenu = activeItem.querySelector('.submenu');
        if (!$subMenu) return;

        const $subMenuItems = $subMenu.querySelectorAll('.category-item');
        if (!$subMenuItems) return;

        $subMenuItems.forEach(function(item) {
            item.classList.remove('is-focussed');
        });
    }

    function closeMenu() {
        // Close all submenus
        $menuItems.forEach(function(item) {
            item.classList.remove('is-focussed');
            const $subItems = item.querySelectorAll('.category-item');

            if ($subItems.length > 0) {
                $subItems.forEach(function(submenuItem) {
                    submenuItem.classList.remove('is-focussed');
                });
            }
        });

        currentLevelCounter = 0;
        isMainMenu = true;

        // Reset focus
        //document.activeElement.blur();
    }

    function setFocus(newIndex) {
        $menuItems[newIndex].querySelector('a').focus({ preventScroll: true });

        openSubMenu($menuItems, newIndex);
    }

    function movePrev() {
        if (isMainMenu) {
            const currentTopIndex = getCurrentMenuListIndex();
            const newFocusIndex = (currentTopIndex > 0) ? currentTopIndex - 1 : $menuItems.length - 1;

            setFocus(newFocusIndex);
        } else {
            const activeElement = document.activeElement.closest(`.parent.level${currentLevelCounter}`);
            activeElement.classList.remove('is-focussed');
            activeElement.querySelector(':scope > a').focus({ preventScroll: true });
            currentLevelCounter--;

            // If focus on MainMenu, set up init variables
            if (currentLevelCounter < 0) {
                isMainMenu = true;
                currentLevelCounter = 0;
            }
        }
    }
    
    function moveNext() {
        if (isMainMenu) {
            const currentTopIndex = getCurrentMenuListIndex();
            const newFocusIndex = (currentTopIndex < $menuItems.length - 1) ? currentTopIndex + 1 : 0;

            setFocus(newFocusIndex);
        } else {
            currentLevelCounter++;
            
            const activeSubmenuItem = document.activeElement.closest(`.level${currentLevelCounter}`);
            activeSubmenuItem.classList.add('is-focussed');
            
            const $subMenu = activeSubmenuItem.querySelector('.submenu');
            if ($subMenu) {
                $subMenu.querySelector('.category-item a').focus({ preventScroll: true });
            } else {
                // If submenu item doesn't have submenu, then move the focus to MainMenu next item
                isMainMenu = true;
                currentLevelCounter = 0;

                // Back to parent MainMenu item
                const parentActiveTopList = document.activeElement.closest('.parent.level0');
                parentActiveTopList.querySelector(':scope > a').focus({ preventScroll: true });

                // Close all submenus
                closeAllSubmenu(parentActiveTopList);

                // Get current top index
                const currentTopIndex = getCurrentMenuListIndex();
                setFocus(currentTopIndex);

                // Move to next MainMenu item
                //const newFocusIndex = (currentTopIndex === $menuItems.length) ? 0 : currentTopIndex + 1;
                //setFocus(newFocusIndex);
            } 
        }
    }

    function moveUpDown(isKeyUp = false) {
        if (isMainMenu) {
            const activeElement = document.activeElement.closest('.level0');
            const $subMenu = activeElement.querySelector('.submenu');

            if(!$subMenu) return;

            $subMenu.querySelector('.category-item a').focus({ preventScroll: true });
            
            // Open first item submenu when keydown is pressed first time
            $subMenu.querySelector('.category-item').classList.add('is-focussed');
            
            currentLevelCounter = 0;
            isMainMenu = false;
        } else {
            // Get current list
            const activeCurrentList = document.activeElement.closest(`.level${currentLevelCounter}`);

            // Get current submenu item
            const activeCurrentItem = document.activeElement.closest(`.level${currentLevelCounter + 1}`);

            // Get all list item in Array format
            const arrayList = activeCurrentList.querySelectorAll(`.level${currentLevelCounter + 1}.category-item`);

            // Get current index
            const arrayMenuItems = Array.prototype.slice.call(arrayList);
            const currentSubmenuIndex = arrayMenuItems.indexOf(activeCurrentItem);

            // Set new index
            let newFocusIndex = 0;
            if (isKeyUp) {
                newFocusIndex = (currentSubmenuIndex > 0) ? currentSubmenuIndex - 1 : arrayList.length - 1;
            } else {
                newFocusIndex = (currentSubmenuIndex < arrayMenuItems.length - 1) ? currentSubmenuIndex + 1 : 0;
            }

            // Show SubMenu
            openSubMenu(arrayList, newFocusIndex);

            // Set focus
            arrayList[newFocusIndex].querySelector('a').focus({ preventScroll: true });
        }
    }

    function resetFocus() {
        if (isMainMenu) {
            // Reset focus
            document.activeElement.blur();

            // Close submenu
            $menuItems.forEach(function(item) {
                item.classList.remove('is-focussed');
            });
        } else {
            // If submenu is opened and active
            const $parentSubmenu = document.activeElement.closest(`.level${currentLevelCounter}.parent`);
            $parentSubmenu?.classList?.remove('is-focussed');
            $parentSubmenu.querySelector('.category-item a').focus();
            currentLevelCounter--;
            
            // If focus is back to main menu
            if (currentLevelCounter <= 0) {
                isMainMenu = true;
                currentLevelCounter = 0;
            }
        }
    }

    function handleKeyDown(e) {
        const key = e.key;

        if (key === 'ArrowDown' || key === 'ArrowUp') e.preventDefault();

        switch(key) {
            case 'ArrowLeft':
                e.preventDefault();
                movePrev();
                break;
            case 'ArrowRight':
                e.preventDefault();
                moveNext();
                break;
            case 'ArrowUp':
                e.preventDefault();
                moveUpDown(true);
                break;
            case 'ArrowDown':
                e.preventDefault();
                moveUpDown();
                break;
            case 'Escape':
                resetFocus();
                break;
        }
    }

    $mainNavigationList.addEventListener('focusin', function() {
        $mainNavigationList.addEventListener(
            'keydown',
            handleKeyDown,
            { once: true }
        );
    });

    $mainNavigationContainer.addEventListener('focusout', function(e) {
        const nextFocused = e.relatedTarget;

        if ($mainNavigationContainer.contains(nextFocused)) return;
        
        closeMenu();

        // Remove listener
        $mainNavigationList.removeEventListener('keydowm', handleKeyDown);
    })
    // .Menu Navigation
});