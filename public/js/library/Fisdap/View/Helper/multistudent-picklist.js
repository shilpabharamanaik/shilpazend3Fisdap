$(function () {
    initFancyFilter();
    initPicklistButtons();
    setPicklistStyling();
    initStudentLists();
    initSearch();
    initControlButtons();
    updateFilterHeader();

    // update the # of students selected
    updateAssignedCount();
});