import { NextResponse } from "next/server";
import executeQuery from "../../../../lib/db";

export async function GET(request: Request) {
  try {
    const ip = request.headers.get("x-forwarded-for") || "unknown";
    const query = `
      INSERT INTO youtube_clicks (ip_address)
      VALUES (?)
    `;
    const values = [ip];

    await executeQuery({ query, values });

    return NextResponse.redirect("https://www.youtube.com/@JavascriptMastery", { status: 302 });
  } catch (error) {
    console.error("Error tracking YouTube click:", error);
    return NextResponse.redirect("https://www.youtube.com/@JavascriptMastery", { status: 302 });
  }
}