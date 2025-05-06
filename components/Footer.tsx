import Image from "next/image";

export default function Footer() {
  return (
    <footer className="mt-10 text-gray-600 text-center">
      <p>Follow us on social media:</p>
      <a
        href="/api/youtube-click"
        target="_blank"
        rel="noopener noreferrer"
        className="inline-block mt-2"
      >
        <Image src="/youtube.svg" alt="YouTube" width={32} height={32} />
      </a>
    </footer>
  );
}