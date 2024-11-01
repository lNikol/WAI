function updateMembersList() {
  let selectedBand = document.querySelector(
    'input[name="zespol_muzyczny"]:checked'
  ).value;

  let beforeMembersList = document.getElementById("before_members_list");
  let parent = beforeMembersList.parentNode;

  let existingMembersList = document.getElementById("members-list");
  if (existingMembersList) parent.removeChild(existingMembersList);

  let membersList = document.createElement("div");
  membersList.id = "members-list";

  let label = document.createElement("label");
  label.setAttribute("for", "ulubiony_czlonek");
  label.textContent = "Ulubiony Członek Zespołu:";
  membersList.appendChild(label);

  membersList.appendChild(document.createElement("br"));

  let sabatonMembers = [
    "Joakim Brodén",
    "Pär Sundström",
    "Chris Rörland",
    "Tommy Johansson",
    "Hannes van Dahl",
  ];

  let threeDaysGraceMembers = [
    "Matt Walst",
    "Brad Walst",
    "Barry Stock",
    "Neil Sanderson",
  ];

  let bandMembers =
    selectedBand === "Sabaton" ? sabatonMembers : threeDaysGraceMembers;

  bandMembers.forEach(function (member) {
    let checkbox = document.createElement("input");
    checkbox.type = "checkbox";
    checkbox.id = member;
    checkbox.name = "ulubieni_czlonkowie";
    checkbox.value = member;

    let checkboxLabel = document.createElement("label");
    checkboxLabel.setAttribute("for", member);
    checkboxLabel.textContent = member;

    membersList.appendChild(checkbox);
    membersList.appendChild(checkboxLabel);
    membersList.appendChild(document.createElement("br"));
  });

  parent.insertBefore(membersList, beforeMembersList.nextSibling);
}
