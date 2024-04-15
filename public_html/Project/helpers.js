function flash(message = "", color = "info") {
    let flash = document.getElementById("flash");
    //create a div (or whatever wrapper we want)
    let outerDiv = document.createElement("div");
    outerDiv.className = "row justify-content-center";
    let innerDiv = document.createElement("div");
    let buttonClose = document.createElement("button");

    buttonClose.type = "button";
    buttonClose.className = "btn-close";
    buttonClose.setAttribute('data-bs-dismiss', 'alert');


    //apply the CSS (these are bootstrap classes which we'll learn later)
    innerDiv.className = `alert alert-${color} alert-dismissible fade show`;
    //set the content
    innerDiv.innerText = message;

    outerDiv.appendChild(innerDiv);
    innerDiv.appendChild(buttonClose);
    //add the element to the DOM (if we don't it merely exists in memory)
    flash.appendChild(outerDiv);
}