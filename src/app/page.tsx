import Footer from "../../components/Footer";
import SignUpForNewsLetter from "../../components/SignUpForNewsletter";

export default function Home() {
  return (
    <div className="max-w-2xl w-full text-center bg-white/95 text-gray-800 rounded-3xl p-10 shadow-xl mx-auto my-10">
      <h1 className="text-4xl text-[#1e3c72] mb-5 font-bold">(not)badtech</h1>
      <p className="text-lg leading-relaxed mb-8 text-gray-600">
        We're reinventing technology for everyday people by listening to your feedback and refining products to be truly user-friendly. Our launch is coming soon – stay in the loop!
      </p>
      <SignUpForNewsLetter />
      <Footer />
    </div>
  );
}