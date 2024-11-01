"use strict";
function updateMembersList() {
    // Uzyskuję wybranego muzyka
    const selectedBand = document.querySelector('input[name="zespol_muzyczny"]:checked').value;
    // Uzyskuję element listy członków
    const beforeMembersList = document.getElementById("before_members_list");
    if (!beforeMembersList) {
        console.error("Nie znaleziono elementu o ID 'before_members_list'.");
        return;
    }
    const parent = beforeMembersList.parentNode;
    // Usuwam istniejącą listę członków, jeśli istnieje
    const existingMembersList = document.getElementById("members-list");
    if (existingMembersList) {
        parent.removeChild(existingMembersList);
    }
    // Tworzę nową listę członków
    const membersList = document.createElement("div");
    membersList.id = "members-list";
    // Tworzę etykietę
    const label = document.createElement("label");
    label.setAttribute("for", "ulubiony_czlonek");
    label.textContent = "Ulubiony Członek Zespołu:";
    membersList.appendChild(label);
    membersList.appendChild(document.createElement("br"));
    // Członkowie zespołu
    const sabatonMembers = [
        "Joakim Brodén",
        "Pär Sundström",
        "Chris Rörland",
        "Tommy Johansson",
        "Hannes van Dahl",
    ];
    const threeDaysGraceMembers = [
        "Matt Walst",
        "Brad Walst",
        "Barry Stock",
        "Neil Sanderson",
    ];
    // Wybór członków zespołu na podstawie wybranej grupy
    const bandMembers = selectedBand === "Sabaton" ? sabatonMembers : threeDaysGraceMembers;
    // Tworzenie checkboxów dla członków zespołu
    bandMembers.forEach((member) => {
        const checkbox = document.createElement("input");
        checkbox.type = "checkbox";
        checkbox.id = member;
        checkbox.name = "ulubieni_czlonkowie";
        checkbox.value = member;
        const checkboxLabel = document.createElement("label");
        checkboxLabel.setAttribute("for", member);
        checkboxLabel.textContent = member;
        membersList.appendChild(checkbox);
        membersList.appendChild(checkboxLabel);
        membersList.appendChild(document.createElement("br"));
    });
    // Wstawiam nową listę członków do DOM
    parent.insertBefore(membersList, beforeMembersList.nextSibling);
}
