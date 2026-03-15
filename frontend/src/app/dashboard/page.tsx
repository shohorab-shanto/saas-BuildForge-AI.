'use client';

import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { useAuthStore } from '@/store/auth';
import api from '@/lib/api';
import { Button } from '@/components/ui/button';
import { Card, CardHeader, CardTitle, CardDescription, CardContent, CardFooter } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { PlusCircle, ExternalLink, Code, Database, Layout, Loader2, RefreshCw } from 'lucide-react';
import { useState } from 'react';
import { useRouter } from 'next/navigation';
import { toast } from 'sonner';
import { Badge } from '@/components/ui/badge';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import Link from 'next/link';

export default function DashboardPage() {
  const { user, logout } = useAuthStore();
  const queryClient = useQueryClient();
  const router = useRouter();
  const [isCreateModalOpen, setIsCreateModalOpen] = useState(false);
  const [newProject, setNewProject] = useState({ name: '', description: '', idea_url: '' });

  const { data: projects, isLoading } = useQuery({
    queryKey: ['projects'],
    queryFn: async () => {
      const response = await api.get('/projects');
      return response.data;
    },
    refetchInterval: (query: any) => {
      const projects = query.state.data;
      const isProcessing = projects?.some((p: any) => p.status !== 'completed' && p.status !== 'failed');
      return isProcessing ? 3000 : false;
    }
  });

  const createProjectMutation = useMutation({
    mutationFn: async (data: any) => {
      const response = await api.post('/projects', data);
      return response.data;
    },
    onSuccess: (data) => {
      queryClient.invalidateQueries({ queryKey: ['projects'] });
      setIsCreateModalOpen(false);
      setNewProject({ name: '', description: '', idea_url: '' });
      toast.success('Project created and generation started!');
      router.push(`/projects/${data.id}`);
    },
    onError: () => {
      toast.error('Failed to create project');
    },
  });

  const retryMutation = useMutation({
    mutationFn: async (id: number) => {
      const response = await api.post(`/projects/${id}/retry`);
      return response.data;
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['projects'] });
      toast.success('Generation restarted!');
    },
    onError: () => {
      toast.error('Failed to restart generation');
    },
  });

  const handleCreateProject = (e: React.FormEvent) => {
    e.preventDefault();
    createProjectMutation.mutate(newProject);
  };

  const getStatusBadge = (status: string) => {
    switch (status) {
      case 'completed':
        return <Badge className="bg-green-100 text-green-700 hover:bg-green-100">Completed</Badge>;
      case 'failed':
        return <Badge variant="destructive">Failed</Badge>;
      case 'pending':
        return <Badge variant="secondary">Pending</Badge>;
      default:
        return <Badge variant="outline" className="animate-pulse">{status.replace('_', ' ')}</Badge>;
    }
  };

  return (
    <div className="min-h-screen bg-gray-50">
      <nav className="bg-white border-b">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="flex justify-between h-16 items-center">
            <div className="text-xl font-bold text-blue-600">BuildForge AI</div>
            <div className="flex items-center space-x-4">
              <span className="text-sm text-gray-600">Welcome, {user?.name}</span>
              <Button variant="outline" size="sm" onClick={logout}>Logout</Button>
            </div>
          </div>
        </div>
      </nav>

      <main className="max-w-7xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
        <div className="flex justify-between items-center mb-8">
          <div>
            <h1 className="text-3xl font-bold text-gray-900">Projects</h1>
            <p className="text-gray-600">Manage your AI-generated software systems</p>
          </div>
          <Dialog open={isCreateModalOpen} onOpenChange={setIsCreateModalOpen}>
            <DialogTrigger asChild>
              <Button className="flex items-center space-x-2">
                <PlusCircle className="w-4 h-4" />
                <span>New Project</span>
              </Button>
            </DialogTrigger>
            <DialogContent>
              <DialogHeader>
                <DialogTitle>Create New Project</DialogTitle>
              </DialogHeader>
              <form onSubmit={handleCreateProject} className="space-y-4">
                <div className="space-y-2">
                  <Label htmlFor="name">Project Name</Label>
                  <Input
                    id="name"
                    value={newProject.name}
                    onChange={(e) => setNewProject({ ...newProject, name: e.target.value })}
                    placeholder="My SaaS Idea"
                    required
                  />
                </div>
                <div className="space-y-2">
                  <Label htmlFor="description">Business Idea (Optional)</Label>
                  <Input
                    id="description"
                    value={newProject.description}
                    onChange={(e) => setNewProject({ ...newProject, description: e.target.value })}
                    placeholder="Describe your startup idea..."
                  />
                </div>
                <div className="space-y-2">
                  <Label htmlFor="idea_url">Website URL (Optional)</Label>
                  <Input
                    id="idea_url"
                    value={newProject.idea_url}
                    onChange={(e) => setNewProject({ ...newProject, idea_url: e.target.value })}
                    placeholder="https://example.com"
                  />
                </div>
                <Button type="submit" className="w-full" disabled={createProjectMutation.isPending}>
                  {createProjectMutation.isPending ? 'Generating...' : 'Start Generation'}
                </Button>
              </form>
            </DialogContent>
          </Dialog>
        </div>

        {isLoading ? (
          <div className="flex justify-center py-20">
            <Loader2 className="w-8 h-8 animate-spin text-blue-600" />
          </div>
        ) : (
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            {projects?.map((project: any) => (
              <Card key={project.id} className="hover:shadow-lg transition-shadow">
                <CardHeader>
                  <div className="flex justify-between items-start">
                    <CardTitle className="text-xl">{project.name}</CardTitle>
                    {getStatusBadge(project.status)}
                  </div>
                  <CardDescription className="line-clamp-2">
                    {project.description || project.idea_url}
                  </CardDescription>
                </CardHeader>
                <CardContent>
                  <div className="flex space-x-4">
                    <div className="flex flex-col items-center space-y-1">
                      <Layout className="w-5 h-5 text-gray-400" />
                      <span className="text-[10px] text-gray-500 uppercase font-semibold">UI</span>
                    </div>
                    <div className="flex flex-col items-center space-y-1">
                      <Database className="w-5 h-5 text-gray-400" />
                      <span className="text-[10px] text-gray-500 uppercase font-semibold">DB</span>
                    </div>
                    <div className="flex flex-col items-center space-y-1">
                      <Code className="w-5 h-5 text-gray-400" />
                      <span className="text-[10px] text-gray-500 uppercase font-semibold">API</span>
                    </div>
                  </div>
                </CardContent>
                <CardFooter className="flex justify-between border-t pt-4">
                  <div className="flex space-x-2">
                    <Link href={`/projects/${project.id}`}>
                      <Button variant="ghost" size="sm" className="text-blue-600">
                        View Details
                      </Button>
                    </Link>
                    {project.status === 'failed' && (
                      <Button 
                        variant="ghost" 
                        size="sm" 
                        className="text-red-600 hover:bg-red-50"
                        onClick={() => retryMutation.mutate(project.id)}
                        disabled={retryMutation.isPending}
                      >
                        <RefreshCw className={`w-3 h-3 mr-1 ${retryMutation.isPending ? 'animate-spin' : ''}`} />
                        Retry
                      </Button>
                    )}
                  </div>
                  <Button variant="ghost" size="sm" className="flex items-center space-x-1">
                    <ExternalLink className="w-3 h-3" />
                    <span>Open App</span>
                  </Button>
                </CardFooter>
              </Card>
            ))}
            {projects?.length === 0 && (
              <div className="col-span-full text-center py-20 bg-white rounded-lg border border-dashed">
                <p className="text-gray-500">No projects yet. Create your first one!</p>
              </div>
            )}
          </div>
        )}
      </main>
    </div>
  );
}
