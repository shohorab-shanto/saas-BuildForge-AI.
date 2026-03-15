'use client';

import { Button } from '@/components/ui/button';
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/components/ui/card';
import { Rocket, Sparkles, Code, Database, Layout, ShieldCheck, ChevronRight } from 'lucide-react';
import Link from 'next/link';
import { motion } from 'framer-motion';

export default function LandingPage() {
  const features = [
    { icon: <Sparkles className="w-6 h-6 text-blue-500" />, title: 'AI Business Analysis', description: 'Enter a URL or idea, and our AI analyzes business models, entities, and workflows.' },
    { icon: <Code className="w-6 h-6 text-purple-500" />, title: 'Full-Stack API Generation', description: 'Automatically generate Laravel 11 APIs with controllers, models, and migrations.' },
    { icon: <Layout className="w-6 h-6 text-pink-500" />, title: 'Next.js Frontend', description: 'Beautiful, responsive ShadCN UI dashboards and landing pages generated instantly.' },
    { icon: <Database className="w-6 h-6 text-green-500" />, title: 'Scalable Architecture', description: 'Production-ready database schemas and microservice-friendly designs.' },
    { icon: <ShieldCheck className="w-6 h-6 text-orange-500" />, title: 'Secure & Modular', description: 'Built-in authentication, authorization, and SOLID principles followed in every line.' },
    { icon: <Rocket className="w-6 h-6 text-red-500" />, title: 'One-Click Deploy', description: 'Docker and Kubernetes configurations ready for AWS, DigitalOcean, or Vercel.' },
  ];

  return (
    <div className="min-h-screen bg-white">
      {/* Navigation */}
      <nav className="border-b bg-white/80 backdrop-blur-md sticky top-0 z-50">
        <div className="max-w-7xl mx-auto px-4 h-16 flex justify-between items-center">
          <div className="text-2xl font-bold text-blue-600 flex items-center space-x-2">
            <Sparkles className="w-6 h-6" />
            <span>BuildForge AI</span>
          </div>
          <div className="flex items-center space-x-6">
            <Link href="/login" className="text-sm font-medium hover:text-blue-600">Login</Link>
            <Link href="/register">
              <Button size="sm">Get Started</Button>
            </Link>
          </div>
        </div>
      </nav>

      {/* Hero Section */}
      <section className="py-24 px-4 overflow-hidden">
        <div className="max-w-7xl mx-auto text-center relative">
          <motion.div
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.5 }}
          >
            <Badge variant="secondary" className="mb-4 py-1 px-4 text-blue-600 bg-blue-50 border-blue-100">
              Introducing BuildForge AI v1.0
            </Badge>
            <h1 className="text-6xl font-extrabold text-gray-900 tracking-tight mb-6">
              Turn Ideas into <span className="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-purple-600">Production-Ready</span> Software
            </h1>
            <p className="text-xl text-gray-600 max-w-3xl mx-auto mb-10">
              BuildForge AI converts your business idea or website URL into a complete SaaS system including architecture, database schema, backend API, and frontend UI.
            </p>
            <div className="flex justify-center space-x-4">
              <Link href="/register">
                <Button size="lg" className="px-8 h-12 text-lg">
                  Start Building Now
                  <ChevronRight className="ml-2 w-5 h-5" />
                </Button>
              </Link>
              <Button size="lg" variant="outline" className="px-8 h-12 text-lg">
                View Sample
              </Button>
            </div>
          </motion.div>
        </div>
      </section>

      {/* Features Grid */}
      <section className="py-24 bg-gray-50 px-4">
        <div className="max-w-7xl mx-auto">
          <div className="text-center mb-16">
            <h2 className="text-3xl font-bold text-gray-900">Everything you need to launch</h2>
            <p className="text-gray-600 mt-4">A complete ecosystem for modern software development.</p>
          </div>
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            {features.map((feature, index) => (
              <motion.div
                key={index}
                whileHover={{ y: -5 }}
                className="transition-all"
              >
                <Card className="h-full border-0 shadow-sm hover:shadow-md transition-shadow">
                  <CardHeader>
                    <div className="mb-4 p-3 bg-white rounded-lg shadow-sm w-fit">
                      {feature.icon}
                    </div>
                    <CardTitle>{feature.title}</CardTitle>
                    <CardDescription className="text-base">
                      {feature.description}
                    </CardDescription>
                  </CardHeader>
                </Card>
              </motion.div>
            ))}
          </div>
        </div>
      </section>

      {/* CTA Section */}
      <section className="py-24 px-4 bg-blue-600">
        <div className="max-w-4xl mx-auto text-center">
          <h2 className="text-4xl font-bold text-white mb-6">Ready to scale your next big idea?</h2>
          <p className="text-blue-100 text-lg mb-10">Join thousands of developers and entrepreneurs building the future with BuildForge AI.</p>
          <Link href="/register">
            <Button size="lg" variant="secondary" className="px-10 h-14 text-lg text-blue-600 font-bold hover:bg-white">
              Get Started for Free
            </Button>
          </Link>
        </div>
      </section>

      {/* Footer */}
      <footer className="py-12 border-t bg-white">
        <div className="max-w-7xl mx-auto px-4 flex flex-col md:flex-row justify-between items-center">
          <div className="text-gray-500 text-sm mb-4 md:mb-0">
            © 2026 BuildForge AI. All rights reserved.
          </div>
          <div className="flex space-x-6 text-gray-400">
            <Link href="#" className="hover:text-blue-600 transition-colors">Privacy Policy</Link>
            <Link href="#" className="hover:text-blue-600 transition-colors">Terms of Service</Link>
            <Link href="#" className="hover:text-blue-600 transition-colors">Documentation</Link>
          </div>
        </div>
      </footer>
    </div>
  );
}

function Badge({ children, variant, className }: any) {
  return (
    <span className={`inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 ${className}`}>
      {children}
    </span>
  );
}
