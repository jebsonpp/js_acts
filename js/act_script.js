// A. Create an array with at least 5 JavaScript objects
let students = [
  { name: "Alex", age: 20, grade: 85 },
  { name: "Bella", age: 22, grade: 90 },
  { name: "Chris", age: 19, grade: 70 },
  { name: "Diana", age: 21, grade: 95 },
  { name: "Ethan", age: 23, grade: 88 }
];

// Helper function to simulate delay
function delay(ms) {
  return new Promise(resolve => setTimeout(resolve, ms));
}

async function runArrayDemo() {
  console.log("=== STARTING ASYNCHRONOUS ARRAY DEMO ===\n");

  // B.	Use the same array and use the forEach() method to print each object in the array. 
  await delay(500);
  console.log("=== B. forEach() ===");
  students.forEach(student => console.log(student));

  // C. Use the same array and demonstrate a sample code using the push() method on the array.
  await delay(500);
  console.log("\n=== C. push() ===");
  students.push({ name: "Fiona", age: 20, grade: 92 });
  console.log(students);

  // D.	Use the same array and demonstrate a sample code using the unshift() method on the array.
  await delay(500);
  console.log("\n=== D. unshift() ===");
  students.unshift({ name: "Zack", age: 18, grade: 80 });
  console.log(students);

  // E.	Use the same array and demonstrate a sample code using the filter() method on the array.
  await delay(500);
  console.log("\n=== E. filter() ===");
  let topStudents = students.filter(student => student.grade > 85);
  console.log(topStudents);

  // F.	Use the same array and demonstrate a sample code using the map() method on the array.
  await delay(500);
  console.log("\n=== F. map() ===");
  let studentNames = students.map(student => student.name);
  console.log(studentNames);

  // G.	Use the same array and demonstrate a sample code using the reduce() method on the array.
  await delay(500);
  console.log("\n=== G. reduce() ===");
  let totalGrades = students.reduce((total, student) => total + student.grade, 0);
  console.log("Total Grades:", totalGrades);

  // H.	Use the same array and demonstrate a sample code using the some() method on the array.
  await delay(500);
  console.log("\n=== H. some() ===");
  let hasLowGrades = students.some(student => student.grade < 75);
  console.log("Has low grades?", hasLowGrades);

  // I.	Use the same array and demonstrate a sample code using the every() method on the array.
  await delay(500);
  console.log("\n=== I. every() ===");
  let allAdults = students.every(student => student.age > 18);
  console.log("All students are adults?", allAdults);

  console.log("\n=== END OF ASYNCHRONOUS ARRAY DEMO ===");
}

// Run the async function
runArrayDemo();