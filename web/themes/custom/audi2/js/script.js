document.addEventListener("DOMContentLoaded", function () {
  // get element by classes .button .js-form-submit .form-submit

  const button = document.querySelector(".button.js-form-submit.form-submit");
  button.value = "";

  // selectionner les divs avec la calss group et tous les grouper dans une div et la placer avant la div ayant pour class "lines_filter" ajouter une barre en chaque div.group

  const div = document.createElement("div");
  const group = document.querySelectorAll(".group");
  const divgroup = document.createElement("div");
  const title = document.createElement("h2");
  title.textContent = "Chiffres clés";
  div.classList.add("key-numbers");
  div.appendChild(title);
  div.appendChild(divgroup);
  divgroup.classList.add("group-container");

  group.forEach((element, index) => {
    divgroup.appendChild(element);
    if (index < group.length - 1) {
      const hr = document.createElement("hr");
      hr.style.borderTop = "1px solid gray"; // Ajoutez cette ligne
      divgroup.appendChild(hr);
    }
  });

  const linesFilter = document.querySelector(".lines_filter");
  const energy_class = document.querySelector(".energy_class");
  const div2 = document.createElement("div");
  div2.classList.add("group2-container");
  div2.appendChild(linesFilter);
  div2.appendChild(energy_class);

  const header_image = document.querySelector(".header_image");
  header_image.after(div);
  div.after(div2);
});
