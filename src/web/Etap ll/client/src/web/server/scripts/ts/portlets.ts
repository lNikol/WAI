$(function () {
  // Umożliwienie sortowania elementów w kolumnach
  $(".myColumn").sortable({
    connectWith: ".myColumn",
    handle: ".portlet-header",
    cancel: ".portlet-toggle",
    placeholder: "portlet-placeholder ui-corner-all",
  });

  // Dodanie klas do portletów
  $(".portlet")
    .addClass("ui-widget ui-widget-content ui-helper-clearfix ui-corner-all")
    .find(".portlet-header")
    .addClass("ui-widget-header ui-corner-all")
    .prepend("<span class='ui-icon ui-icon-plusthick portlet-toggle'></span>");

  // Obsługa kliknięcia na ikonie przełączania
  $(".portlet-toggle").on("click", function () {
    const icon = $(this);
    icon.toggleClass("ui-icon-minusthick ui-icon-plusthick");
    icon.closest(".portlet").find(".portlet-content").toggle();
  });

  // Ukrycie zawartości portletów na początku
  $(".portlet-content").hide();
});
