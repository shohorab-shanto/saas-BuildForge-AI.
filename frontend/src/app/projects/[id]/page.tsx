'use client';

import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { useParams } from 'next/navigation';
import api from '@/lib/api';
import { Card, CardHeader, CardTitle, CardContent, CardDescription } from '@/components/ui/card';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Loader2, Code, Database, Layout, Server, FileCode, RefreshCw } from 'lucide-react';
import { useState } from 'react';
import { toast } from 'sonner';

export default function ProjectViewerPage() {
  const { id } = useParams();
  const queryClient = useQueryClient();
  const [activeFile, setActiveFile] = useState<any>(null);

  const { data: project, isLoading } = useQuery({
    queryKey: ['project', id],
    queryFn: async () => {
      const response = await api.get(`/projects/${id}`);
      return response.data;
    },
    refetchInterval: (query: any) => {
      const project = query.state.data;
      return (project?.status !== 'completed' && project?.status !== 'failed') ? 2000 : false;
    }
  });

  const retryMutation = useMutation({
    mutationFn: async () => {
      const response = await api.post(`/projects/${id}/retry`);
      return response.data;
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['project', id] });
      toast.success('Generation restarted!');
    },
    onError: () => {
      toast.error('Failed to restart generation');
    }
  });

  const getStatusBadge = (status: string) => {
    switch (status) {
      case 'completed':
        return <Badge className="bg-green-100 text-green-700 hover:bg-green-100">Completed</Badge>;
      case 'failed':
        return <Badge variant="destructive">Failed</Badge>;
      case 'pending':
        return <Badge variant="secondary">Pending</Badge>;
      default:
        return <Badge variant="outline" className="animate-pulse">{status?.replace('_', ' ') || 'Unknown'}</Badge>;
    }
  };

  if (isLoading) {
    return (
      <div className="flex items-center justify-center min-h-screen">
        <Loader2 className="w-8 h-8 animate-spin text-blue-600" />
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-gray-50 pb-12">
      <div className="bg-white border-b sticky top-0 z-10">
        <div className="max-w-7xl mx-auto px-4 h-16 flex items-center justify-between">
          <div className="flex items-center space-x-4">
            <h1 className="text-xl font-bold">{project.name}</h1>
            {getStatusBadge(project.status)}
            {project.status === 'failed' && (
              <Button 
                variant="outline" 
                size="sm" 
                onClick={() => retryMutation.mutate()}
                disabled={retryMutation.isPending}
                className="text-red-600 border-red-200 hover:bg-red-50"
              >
                <RefreshCw className={`w-4 h-4 mr-2 ${retryMutation.isPending ? 'animate-spin' : ''}`} />
                Retry Generation
              </Button>
            )}
          </div>
        </div>
      </div>

      <main className="max-w-7xl mx-auto py-8 px-4 grid grid-cols-1 lg:grid-cols-3 gap-8">
        {/* Left Column: Progress & Info */}
        <div className="lg:col-span-1 space-y-6">
          <Card>
            <CardHeader>
              <CardTitle>Generation Status</CardTitle>
              <CardDescription>Real-time progress of your AI build</CardDescription>
            </CardHeader>
            <CardContent className="space-y-4">
              <StatusStep label="Idea Analysis" status={project.status} activeStatus="analyzing" completedStatus={['analyzed', 'architecting', 'architected', 'generating_schema', 'schema_generated', 'generating_api', 'generating_frontend', 'completed']} />
              <StatusStep label="Architecture Design" status={project.status} activeStatus="architecting" completedStatus={['architected', 'generating_schema', 'schema_generated', 'generating_api', 'generating_frontend', 'completed']} />
              <StatusStep label="Database Schema" status={project.status} activeStatus="generating_schema" completedStatus={['schema_generated', 'generating_api', 'generating_frontend', 'completed']} />
              <StatusStep label="Backend API" status={project.status} activeStatus="generating_api" completedStatus={['generating_frontend', 'completed']} />
              <StatusStep label="Frontend UI" status={project.status} activeStatus="generating_frontend" completedStatus={['completed']} />
              
              {project.status === 'failed' && (
                <div className="pt-4 border-t">
                  <p className="text-xs text-red-500 mb-3 font-medium">The generation process encountered an error.</p>
                  <Button 
                    variant="destructive" 
                    size="sm" 
                    className="w-full"
                    onClick={() => retryMutation.mutate()}
                    disabled={retryMutation.isPending}
                  >
                    <RefreshCw className={`w-4 h-4 mr-2 ${retryMutation.isPending ? 'animate-spin' : ''}`} />
                    Restart Build Pipeline
                  </Button>
                </div>
              )}
            </CardContent>
          </Card>

          {project.architecture_json && (
            <Card>
              <CardHeader>
                <CardTitle className="text-sm flex items-center">
                  <Server className="w-4 h-4 mr-2" />
                  System Architecture
                </CardTitle>
              </CardHeader>
              <CardContent className="text-xs space-y-2">
                <div><span className="font-semibold">Type:</span> {project.architecture_json.architecture}</div>
                <div><span className="font-semibold">Scaling:</span> {project.architecture_json.scaling_strategy}</div>
                <div><span className="font-semibold">Cache:</span> {project.architecture_json.caching_layer}</div>
              </CardContent>
            </Card>
          )}
        </div>

        {/* Right Column: Code & Details */}
        <div className="lg:col-span-2">
          <Tabs defaultValue="files" className="w-full">
            <TabsList className="grid w-full grid-cols-2 mb-6">
              <TabsTrigger value="files" className="flex items-center">
                <FileCode className="w-4 h-4 mr-2" />
                Generated Files
              </TabsTrigger>
              <TabsTrigger value="schema" className="flex items-center">
                <Database className="w-4 h-4 mr-2" />
                Database Schema
              </TabsTrigger>
            </TabsList>

            <TabsContent value="files">
              <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div className="md:col-span-1 border rounded-lg bg-white overflow-hidden h-[600px] overflow-y-auto">
                  <div className="p-3 bg-gray-50 border-b text-xs font-bold uppercase text-gray-500">Filesystem</div>
                  {project.generated_files?.map((file: any) => (
                    <button
                      key={file.id}
                      onClick={() => setActiveFile(file)}
                      className={`w-full text-left p-3 text-sm hover:bg-blue-50 transition-colors border-b ${activeFile?.id === file.id ? 'bg-blue-50 border-r-4 border-r-blue-600' : ''}`}
                    >
                      <div className="font-medium truncate">{file.file_path.split('/').pop()}</div>
                      <div className="text-[10px] text-gray-400 truncate">{file.file_path}</div>
                    </button>
                  ))}
                  {(!project.generated_files || project.generated_files.length === 0) && (
                    <div className="p-8 text-center text-gray-400 text-sm italic">
                      Files will appear here as they are generated...
                    </div>
                  )}
                </div>

                <div className="md:col-span-2 border rounded-lg bg-gray-900 h-[600px] relative overflow-hidden">
                  {activeFile ? (
                    <div className="h-full flex flex-col">
                      <div className="p-2 bg-gray-800 border-b border-gray-700 text-xs text-gray-300 font-mono flex justify-between">
                        <span>{activeFile.file_path}</span>
                        <Badge variant="outline" className="text-[10px] h-4 border-gray-600 text-gray-400">{activeFile.file_type}</Badge>
                      </div>
                      <pre className="p-4 text-xs text-blue-300 font-mono overflow-auto h-full scrollbar-thin scrollbar-thumb-gray-700">
                        <code>{activeFile.content}</code>
                      </pre>
                    </div>
                  ) : (
                    <div className="h-full flex flex-col items-center justify-center text-gray-500 space-y-4">
                      <Code className="w-12 h-12 opacity-20" />
                      <p className="text-sm">Select a file to view code</p>
                    </div>
                  )}
                </div>
              </div>
            </TabsContent>

            <TabsContent value="schema">
              <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                {project.schema_json?.tables.map((table: any, idx: number) => (
                  <Card key={idx}>
                    <CardHeader className="py-4">
                      <CardTitle className="text-base flex items-center justify-between">
                        <span className="font-mono text-blue-600">{table.name}</span>
                        <Badge variant="outline" className="text-[10px]">Table</Badge>
                      </CardTitle>
                    </CardHeader>
                    <CardContent className="text-xs">
                      <ul className="space-y-1">
                        {table.columns.map((col: any, cidx: number) => (
                          <li key={cidx} className="flex justify-between border-b border-gray-50 py-1">
                            <span className="font-mono">{col.name}</span>
                            <span className="text-gray-400">{col.type}</span>
                          </li>
                        ))}
                      </ul>
                    </CardContent>
                  </Card>
                ))}
              </div>
            </TabsContent>
          </Tabs>
        </div>
      </main>
    </div>
  );
}

function StatusStep({ label, status, activeStatus, completedStatus }: any) {
  const isCompleted = completedStatus.includes(status);
  const isActive = status === activeStatus;

  return (
    <div className="flex items-center space-x-3">
      <div className={`w-6 h-6 rounded-full flex items-center justify-center text-[10px] font-bold ${
        isCompleted ? 'bg-green-100 text-green-600' :
        isActive ? 'bg-blue-100 text-blue-600 animate-pulse' :
        'bg-gray-100 text-gray-400'
      }`}>
        {isCompleted ? '✓' : isActive ? '●' : '○'}
      </div>
      <span className={`text-sm ${isActive ? 'font-bold text-gray-900' : isCompleted ? 'text-gray-600' : 'text-gray-400'}`}>
        {label}
      </span>
    </div>
  );
}
