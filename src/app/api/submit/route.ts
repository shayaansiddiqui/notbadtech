import { NextResponse } from "next/server";
import executeQuery from "../../../../lib/db";

export async function POST(request: Request) {
  try {
    const { firstName, lastName, email, phone, city, state, zip, country } = await request.json();

    const query = `
      INSERT INTO submissions (first_name, last_name, email, phone, city, state, zip, country)
      VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    `;
    const values = [firstName, lastName, email, phone, city || null, state || null, zip || null, country || null];

    await executeQuery({ query, values });

    return NextResponse.json({ success: true }, { status: 200 });
  } catch (error) {
    console.error("Error saving submission:", error);
    return NextResponse.json({ success: false, error: "Failed to save submission" }, { status: 500 });
  }
}