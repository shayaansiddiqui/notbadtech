import type { Metadata } from "next";
import "./globals.css";
import Script from "next/script";

export const metadata: Metadata = {
  title: "(not)badtech - Launching Soon",
  description: "Reinventing technology for everyday people. Stay informed about our launch!",
};

export default function RootLayout({
  children,
}: {
  children: React.ReactNode;
}) {
  return (
    <html lang="en">
      <head>
        <link
          rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css"
        />
      </head>
      <body>
        {children}
        <Script
          src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"
          strategy="lazyOnload"
        />
      </body>
    </html>
  );
}