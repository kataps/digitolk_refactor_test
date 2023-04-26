# DigitalTolk Code test (REFACTOR)

## Type Hints and Return type

- Some methods/functions don't have type hints in their arguments and return types.
Example:

```
function myMethod(int $id): array;
```
I also noticed that all of them are using the `response()` method in `BookingController`.
`response()` method will convert the passed argument value into an appropriate response. Meaning, if you passed an array type data, it will be converted to JSON type and text to string as well.
It will be confusing to others, especially for newcomers who will be involved in the project and be assigned to maintain the system.
The good thing is, it has a special comment that documents the parameters and return types on each method/function.


## Method/Function Statements

- Check the `distanceFeed()` method at line 197 up to 254 in `BookingController`, it has multiple `if` statements and it's too long.
If I'm going to fix this, I'm going to create a separate method/function that handles getting input files. With this, we made it non-overwhelming, readable, and easier to understand for other developers.
Also, I prefer using `$request->has('<yourParamNameHere>')` instead of using `isset()`, just to utilize Laravel features.

## Comparison Operators

- I prefer comparing values with data type, meaning they must have the same value and data type.
```
$input !== ''
```

Take a look at `distanceFeed()` at line 199 again just for example.
We know that `data['param_name']` came from Request, right? So it will be presented as NULL value when the input is empty. A NULL comparing to a string is confusing. Though we can use `empty()` method if you're just checking if it has a value or not.

## Variables and Function Naming Convention

- There's no official PSR (PHP Standard Recommendation) that mandates snake_cased variable names and camelCased function but it's generally accepted as best practice and recommended by several PSRs e.g. PSR-1 and PSR-12 extended.
Also, name should be aligned based on its value. For example:

```
$user_type
// With this name, I assumed that it referenced to a string values like "admin" // or "superadmin" as a user type.
```


```
$user_type_id
// I assumed that this is an ID of a reference user type.

```
## Validation

- I noticed that in `BaseRepository`, it has `validator()`, but it's not implemented.
It's mandatory to have validation of inputs, not just for requirements but to avoid some known security vulnerabilities such as SQL injection, RCE, etc...


## Improving `getAll()` in `BookingRepository`

- The `getAll()` implementation in `BookingRepository` has repeated `if` statements for the admin or super admin. Laravel has `when()` built-in function which you can use for conditional eloquent. The 1st argument is the condition and the 2nd argument will be executed when the statement is true and the 3rd argument is when it's false.
For me, this is easier to understand than before.

## Design Pattern

- It's a good thing that it uses Repository Design pattern. I'm new to this, I just read it somewhere and made a personal project with this pattern just to get familiar. Honestly, we don't do this in my recent company, but I'm interested in digging deeper.
