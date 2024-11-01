"use strict";
// Upewniam się, że jQuery jest załadowane i gotowe do użycia
$(document).ready(() => {
    $(".filter").click(function () {
        const group = $(this).data("group"); // Rzutuję typ, aby TypeScript rozpoznał jako string
        $("img").hide();
        $("." + group).show();
    });
});
