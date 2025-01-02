// Upewniam się, że jQuery jest załadowane i gotowe do użycia
$(document).ready(() => {
  $(".filter").click(function () {
    const group: string = $(this).data("group") as string; // Rzutuję typ, aby TypeScript rozpoznał jako string
    $("img").hide();
    $("." + group).show();
  });
});
