//checks a var if it's empty
function isEmpty(Value) {
	//if variable is null
	if (Value == null)
	{
		return true;
	}
	//if variable is of type String
	if (typeof Value === "string" || Value instanceof String)
	{
		//if string is empty
		return (Value.length === 0);
	}
	return false;
}
//switches display from block to none and back again
function alternate(stuff) {
	if (stuff.style.display === "none")
	{
		displayType("block", stuff.id);
	}
	else
	{
		displayType("none", stuff.id);
	}
}
//activates on button press
function displayOrHide(id) {
	alternate(document.getElementById(id));
	alternate(document.getElementById("changeForm"));
	alternate(document.getElementById("profDetails"));
}
//removes all colours and sets one
function convert(Input, Colour) {
	//set colour if not already set
	if (!Input.classList.contains(Colour))
	{
		Input.classList.add(Colour);
	}
	//removes all other colours
	if (Input.classList.contains("blue") && "blue" != Colour)
	{
		Input.classList.remove("blue");
	}
	if (Input.classList.contains("green") && "green" != Colour)
	{
		Input.classList.remove("green");
	}
	if (Input.classList.contains("red") && "red" != Colour)
	{
		Input.classList.remove("red");
	}
}
//sets colours to input fields depending on values
function comparison(Input, Value) {
	if (Input.value != Value)
	{
		if (Input.classList.contains("red"))
		{
			Input.classList.remove("red");
		}
		Input.classList.remove("green");
		Input.classList.add("blue");
	}
	else if (Input.classList.contains("blue"))
	{
		if (Input.classList.contains("red"))
		{
			Input.classList.remove("red");
		}
		Input.classList.add("green");
		Input.classList.remove("blue");
	}
	if (isEmpty(Input.value))
	{
		convert(Input, "red");
	}
}
//validate all characters for message box
function pswValidate(input, type, regex) {
	//was named pswValidate but is used for nameChange form as well
	if	(input.value.match(regex))
	{  
		type.classList.remove("invalid");
		type.classList.add("valid");
	}
	else
	{
		type.classList.remove("valid");
		type.classList.add("invalid");
	}
}
//validate for starting characters message box
function capValidate(input, type, regex) {
	//was named pswValidate but is used for nameChange form as well
	if	(input.value.charAt(0).match(regex))
	{  
		type.classList.remove("invalid");
		type.classList.add("valid");
	}
	else
	{
		type.classList.remove("valid");
		type.classList.add("invalid");
	}
}
function displayType(type, id) {
	document.getElementById(id).style.display = type;
}